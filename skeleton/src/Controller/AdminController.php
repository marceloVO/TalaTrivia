<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Category;
use App\Entity\Question;
use App\Entity\Trivia;
use App\Entity\Answer;
use App\Entity\User;


#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('', name: 'admin_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }
    //Categorias
    //Listado de categorias
    #[Route('/categories_admin', name: 'categories_list', methods: ['GET'])]
    public function listCategories(EntityManagerInterface $em): Response
    {
        
        $categories = $em->getRepository(Category::class)->findAll();
        $data = array_map(function (Category $c) {
            return ['id' => $c->getId(), 'name' => $c->getName()];
        }, $categories);

        return $this->render('admin/categories.html.twig', ['categories' => $data]);
    }
    //creacion de categoria mediante modal en la vista de categorias
    #[Route('/categories_add', name: 'categories_create', methods: ['POST'])]
    public function createCategory(Request $req, EntityManagerInterface $em): Response
    {
        $name = null;
        $content = (string) $req->getContent();
        if ($content !== '') {
            $payload = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($payload['name'])) {
                $name = trim((string) $payload['name']);
            }
        }
        if ($name === null) {
            $name = trim((string) $req->request->get('name', ''));
        }
        if ($name === '') {
            $this->addFlash('error', 'El nombre es requerido');
            return $this->redirectToRoute('categories_list');
        }
        $c = new Category();
        $c->setName($name);
        $em->persist($c);
        $em->flush();
        $this->addFlash('success', 'Categoría creada');
        return $this->redirectToRoute('categories_list');
    }
    //funcion para ocupar el modal y poder editar la categoria seleccionada
    #[Route('/edit_categories', name: 'categories_edit', methods: ['POST'])]
    public function editCategories(Request $req, EntityManagerInterface $em): Response
    {
        $id = $req->request->get('idCategoria');
        $name = $req->request->get('nameEdit');
        if ($id === null && $name === null) {
            $content = (string) $req->getContent();
            if ($content !== '') {
                $payload = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $id = $payload['idCategoria'] ?? $payload['id'] ?? null;
                    $name = $payload['nameEdit'] ?? null;
                }
            }
        }
        if (empty($id)) {
            $this->addFlash('error', 'ID de categoría faltante');
            return $this->redirectToRoute('categories_list');
        }
        $category = $em->getRepository(Category::class)->find((int) $id);
        if (!$category) {
            $this->addFlash('error', 'Categoría no encontrada');
            return $this->redirectToRoute('categories_list');
        }
        if (empty($name)) {
            $this->addFlash('error', 'El nombre es requerido');
            return $this->redirectToRoute('categories_list');
        }
        $category->setName((string) $name);
        $em->persist($category);
        $em->flush();

        $this->addFlash('success', 'Categoría actualizada');
        return $this->redirectToRoute('categories_list');
    }

    //funcion para eliminar categoria seleccionada
    #[Route('/delete_categories/{id}', name: 'categories_delete', methods: ['DELETE'])]
    public function deleteCategories(int $id, EntityManagerInterface $em, Request $request): Response
    {
        $category = $em->getRepository(Category::class)->find($id);
        if (!$category) {
            return $this->json(['error' => 'Categoría no encontrada'], 404);
        }
        // Bloquear si tiene hijos (si la relación es OneToMany(questions))
        if (!$category->getQuestions()->isEmpty()) {            
            return $this->json(['error' => 'No se puede eliminar: La categoría tiene preguntas asociadas.'], 409);
        }

        $em->remove($category);
        $em->flush();
        return $this->json(['success' => true]);
    }
    //fin Categorias
    //Preguntas
    #[Route('/questions_admin', name: 'questions_list', methods: ['GET'])]
    public function listQuestions(EntityManagerInterface $em, Request $request): Response
    {
        $limit = 10;
        $currentPage = $request->query->getInt('page', 1);
        $offset = ($currentPage - 1) * $limit;
        $questionRepository = $em->getRepository(Question::class);
        $questions = $questionRepository->findBy(
            [], 
            ['id' => 'ASC'], 
            $limit, 
            $offset 
        );
        $totalItems = $questionRepository->count([]);
        $totalPages = ceil($totalItems / $limit);
        $data = array_map(function (Question $q) {
            return [
                'id' => $q->getId(),
                'text' => $q->getText(),
                'difficulty' => $q->getDifficulty(),
                'score' => $q->getScore(),
                'category' => $q->getCategory(),
            ];
        }, $questions);

        $categories = $em->getRepository(Category::class)->findAll();
        $categorias = array_map(function (Category $c) {
            return ['id' => $c->getId(), 'name' => $c->getName()];
        }, $categories);
        
        $parameters = [
            'questions' => $data,
            'categories' => $categorias,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'path_name' => 'questions_list', 
            'total_items' => $totalItems,
            'limit' => $limit
        ];
        return $this->render('admin/questions.html.twig', $parameters);
    }
    //funcion para crear preguntas mediante modal en la vista de preguntas
    #[Route('/questions_add', name: 'questions_create', methods: ['POST'])]
    public function createQuestion(Request $req, EntityManagerInterface $em): Response
    {
        // 1. Obtener los datos sin importar si vienen de JSON o de un formulario
        $payload = [];
        $content = (string) $req->getContent();

        if ($content !== '') {
            $payload = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Si el JSON es inválido, registramos un error y usamos los datos del request
                $payload = [];
            }
        }
        
        // Si no se pudo leer JSON o JSON está vacío, usamos los datos del formulario (POST)
        if (empty($payload)) {
            $payload = $req->request->all();
        }

        // 2. Definir los campos requeridos
        $requiredFields = ['text', 'difficulty', 'score', 'category_id'];

        // 3. Validación de campos requeridos
        foreach ($requiredFields as $f) {
            // Usamos isset y empty para asegurar que el valor exista y no esté vacío (cadenas vacías)
            if (!isset($payload[$f]) || (is_string($payload[$f]) && trim($payload[$f]) === '')) {
                // Usamos addFlash y redirect, igual que en createCategory, para peticiones de formulario
                $this->addFlash('error', "El campo '$f' es requerido.");
                return $this->redirectToRoute('questions_list');
            }
        }
        
        // 4. Buscar la categoría
        $category = $em->getRepository(Category::class)->find((int)$payload['category_id']);
        if (!$category) {
            $this->addFlash('error', 'La categoría seleccionada no fue encontrada.');
            return $this->redirectToRoute('questions_list');
        }

        // 5. Crear y persistir la pregunta
        $q = new Question();
        $q->setText(trim((string)$payload['text']));
        
        // Aseguramos que la dificultad y el score sean enteros
        $q->setDifficulty((int)$payload['difficulty']);
        $q->setScore((int)$payload['score']);
        
        $q->setCategory($category);

        $em->persist($q);
        $em->flush();

        $this->addFlash('success', 'Pregunta creada exitosamente.');
        return $this->redirectToRoute('questions_list');
    }
    #[Route('/questions_edit', name: 'questions_edit', methods: ['POST'])]
    public function createQuestionEdit(Request $req, EntityManagerInterface $em): Response
    {
        
        $text = $req->request->get('textE');
        $difficulty = $req->request->get('difficultyE');
        $score = $req->request->get('scoreE');
        $category_id = $req->request->get('category_idE');
        $idQuestions = $req->request->get('idQuestionsE');
        
       
        
        // 4. Buscar la categoría
        $category = $em->getRepository(Category::class)->find((int)$category_id);
        if (!$category) {
            $this->addFlash('error', 'La categoría seleccionada no fue encontrada.');
            return $this->redirectToRoute('questions_list');
        }
        
        $questions = $em->getRepository(Question::class)->find((int) $idQuestions);
       
        if (!$questions) {
            $this->addFlash('error', 'Pregunta no encontrada');
            return $this->redirectToRoute('questions_list');
        }
        $questions->setText(trim((string)$text));
        
        $questions->setDifficulty((int)$difficulty);
        $questions->setScore((int)$score);
        
        $questions->setCategory($category);

        $em->persist($questions);
        $em->flush();
        
        
        $this->addFlash('success', 'Pregunta creada exitosamente.');
        return $this->redirectToRoute('questions_list');
    }
    //fin Preguntas

    //trivia 
    //Listado de trivias para el admin
    #[Route('/trivia_list', name: 'trivia_list', methods: ['GET'])]
    public function listTrivias(EntityManagerInterface $em, Request $request): Response
    {
        $limit = 10;
        $currentPage = $request->query->getInt('page', 1);
        $offset = ($currentPage - 1) * $limit;
        $triviaRepository = $em->getRepository(Trivia::class);
        $trivias = $triviaRepository->findBy(
            [], 
            ['id' => 'ASC'], 
            $limit,
            $offset
        );
        $totalItems = $triviaRepository->count([]);
        $totalPages = ceil($totalItems / $limit);
        $data = array_map(function (Trivia $t) {
            return [
                'id' => $t->getId(), 
                'name' => $t->getName(), 
                'questions' => $t->getQuestions()->toArray()
            ];
        }, $trivias);
        
        $questions = $em->getRepository(Question::class)->findAllOrderedByCategoryAndId();


        $parameters = [
            'trivias' => $data,
            'questions' => $questions,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'path_name' => 'trivia_list',
            'total_items' => $totalItems,
            'limit' => $limit
        ];
        return $this->render('admin/trivias.html.twig', $parameters);
    }
    //creacion de trivia
    #[Route('/trivia_add', name: 'trivia_create', methods: ['POST'])]
    public function createTrivia(Request $req, EntityManagerInterface $em): Response
    {
        $triviaName = $req->request->get('name');
        $allPostData = $req->request->all();
        $questionsIds = $allPostData['questions_ids'] ?? [];
        
        if (empty($questionsIds)) {
            $this->addFlash('error', 'Debes seleccionar al menos una pregunta.');
            return $this->redirectToRoute('trivia_list');
        }

        $trivia = new Trivia();
        $trivia->setName($triviaName);
        $questionRepository = $em->getRepository(Question::class);

        foreach ($questionsIds as $id) {
            $question = $questionRepository->find((int) $id);
            if ($question) {
                $trivia->addQuestion($question); 
            }
        }
        $em->persist($trivia);
        $em->flush();

        $this->addFlash('success', 'Pregunta creada exitosamente.');
        return $this->redirectToRoute('trivia_list');
    }
    //funcion para editar la trivia seleccionada
    #[Route('/trivia_edit/{id}', name: 'trivia_edit', methods: ['POST'])]
    public function editTrivia(Request $req, EntityManagerInterface $em, Trivia $trivia): Response
    {
        $triviaName = $req->request->get('name');
        $allPostData = $req->request->all();
        
        $newQuestionsIds = $allPostData['questions_ids'] ?? [];

        if (empty($triviaName)) {
            $this->addFlash('error', 'El nombre de la trivia no puede estar vacío.');
            return $this->redirectToRoute('trivia_list', ['id' => $trivia->getId()]);
        }
        
        $trivia->setName($triviaName);
        $questionRepository = $em->getRepository(Question::class);
        $currentQuestionIds = $trivia->getQuestions()->map(fn(Question $q) => $q->getId())->toArray();

        $newQuestionsIds = array_map('intval', (array) $newQuestionsIds);
        
        $questionsToRemoveIds = array_diff($currentQuestionIds, $newQuestionsIds);

        foreach ($trivia->getQuestions() as $question) {
            if (in_array($question->getId(), $questionsToRemoveIds)) {
                $trivia->removeQuestion($question);
            }
        }
        
        $questionsToAddIds = array_diff($newQuestionsIds, $currentQuestionIds);

        foreach ($questionsToAddIds as $id) {
            $question = $questionRepository->find($id);
            if ($question) {
                $trivia->addQuestion($question);
            }
        }
        $em->persist($trivia); 
        $em->flush();

        $this->addFlash('success', 'Trivia "' . $triviaName . '" actualizada exitosamente.');
        return $this->redirectToRoute('trivia_list');
    }
    
    //Fin Trivia

    //respuestas
    #[route('/answer_list', name: 'answer_list', methods: ['GET'])]
    public function listAnswers(EntityManagerInterface $em, Request $request): Response
    {
        $limit = 10;
        $currentPage = $request->query->getInt('page', 1);
        $offset = ($currentPage - 1) * $limit;
        $answerRepository = $em->getRepository(Answer::class);
        $answers = $answerRepository->findBy(
            [], 
            ['id' => 'ASC'], 
            $limit, 
            $offset 
        );
        $totalItems = $answerRepository->count([]);
        $totalPages = ceil($totalItems / $limit);
        $data = array_map(function (Answer $a) {
            return [
                'id' => $a->getId(),
                'text' => $a->getText(),
                'is_correct' => $a->isCorrect(), 
                'question' => $a->getQuestion(), 
            ];
        }, $answers);
        
        $questions = $em->getRepository(Question::class)->findAllOrderedByCategoryAndId();


        $parameters = [
            'answers' => $data,
            'questions' => $questions,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'path_name' => 'answer_list',
            'total_items' => $totalItems,
            'limit' => $limit
        ];
        
        return $this->render('admin/answer.html.twig', $parameters);
    }
    //creacion de respuestas
    #[Route('/answer_add', name: 'answer_create', methods: ['POST'])]
    public function createAnswer(Request $req, EntityManagerInterface $em): Response
    {
        $text = $req->request->get('nombreRespuesta');
        $isCorrect = $req->request->get('isCorrect');
        $questionId = $req->request->get('questions_ids');

        if (empty($text) || !in_array($isCorrect, ['0', '1'], true) || empty($questionId)) {
            $this->addFlash('error', 'Todos los campos son obligatorios y deben ser válidos.');
            return $this->redirectToRoute('answer_list');
        }

        $question = $em->getRepository(Question::class)->find((int)$questionId);
        if (!$question) {
            $this->addFlash('error', 'La pregunta seleccionada no fue encontrada.');
            return $this->redirectToRoute('answer_list');
        }

        $answer = new Answer();
        $answer->setText(trim((string)$text));
        $answer->setIsCorrect($isCorrect === '1' ? true : null); // Convertimos '1' a true y '0' a null
        $answer->setQuestion($question);

        try {
            $em->persist($answer);
            $em->flush();
            $this->addFlash('success', 'Respuesta creada exitosamente.');
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $this->addFlash('error', 'Error: Solo puede haber una respuesta correcta por pregunta.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Ocurrió un error al crear la respuesta.');
        }

        return $this->redirectToRoute('answer_list');
    }

    //edicion de respuestas
    #[Route('/answer_edit', name: 'answer_edit', methods: ['POST'])]
    public function editAnswer(Request $req, EntityManagerInterface $em): Response
    {
        $id = $req->request->get('idAnswer');
        $text = $req->request->get('nombreRespuestaE');
        $isCorrect = $req->request->get('isCorrectE');
        $questionId = $req->request->get('questions_idsE');

        if (empty($id) || empty($text) || !in_array($isCorrect, ['0', '1'], true) || empty($questionId)) {
            $this->addFlash('error', 'Todos los campos son obligatorios y deben ser válidos.');
            return $this->redirectToRoute('answer_list');
        }

        $answer = $em->getRepository(Answer::class)->find((int)$id);
        if (!$answer) {
            $this->addFlash('error', 'Respuesta no encontrada.');
            return $this->redirectToRoute('answer_list');
        }

        $question = $em->getRepository(Question::class)->find((int)$questionId);
        if (!$question) {
            $this->addFlash('error', 'La pregunta seleccionada no fue encontrada.');
            return $this->redirectToRoute('answer_list');
        }

        $answer->setText(trim((string)$text));
        $answer->setIsCorrect($isCorrect === '1' ? true : null); // Convertimos '1' a true y '0' a null
        $answer->setQuestion($question);

        try {
            $em->persist($answer);
            $em->flush();
            $this->addFlash('success', 'Respuesta actualizada exitosamente.');
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $this->addFlash('error', 'Error: Solo puede haber una respuesta correcta por pregunta.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Ocurrió un error al actualizar la respuesta.');
        }

        return $this->redirectToRoute('answer_list');
    }
    //eliminar respuesta seleccionada
    #[Route('/delete_answer/{id}', name: 'answer_delete', methods: ['DELETE'])]
    public function deleteAnswer(int $id, EntityManagerInterface $em): Response
    {
        $answer = $em->getRepository(Answer::class)->find($id);
        if (!$answer) {
            return $this->json(['error' => 'Respuesta no encontrada'], 404);
        }

        $em->remove($answer);
        $em->flush();
        return $this->json(['success' => true]);
    }
    //fin respuestas

    //listado de usuarios
    #[Route('/user_list', name: 'user_list', methods: ['GET'])]
    public function listUsers(EntityManagerInterface $em, Request $request): Response
    {
        $limit = 10;
        $currentPage = $request->query->getInt('page', 1);
        $offset = ($currentPage - 1) * $limit;
        $userRepository = $em->getRepository(User::class);
        $usersData = $userRepository->findBy(
            [], 
            ['id' => 'ASC'], 
            $limit, 
            $offset 
        );
        $totalItems = $userRepository->count([]);
        $totalPages = ceil($totalItems / $limit);
        return $this->render('admin/users.html.twig', [
            'users' => $usersData,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'path_name' => 'user_list', 
            'total_items' => $totalItems,
            'limit' => $limit
        ]);
    }
    //fin listado de usuarios
}
