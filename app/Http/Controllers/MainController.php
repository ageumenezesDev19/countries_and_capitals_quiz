<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class MainController extends Controller
{
    private array $app_data;

    public function __construct()
    {
        $this->app_data = require(app_path('app_data.php'));
    }

    public function startGame(): View
    {
        return view('home', ['app_data' => $this->app_data]);
    }

    public function prepareGame(Request $request)
{
    $request->validate([
        'total_questions' => 'required|integer|min:3|max:30',
    ], [
        'total_questions.required' => 'Please enter the total number of questions.',
        'total_questions.integer' => 'The total number of questions must be an integer.',
        'total_questions.min' => 'The total number of questions must be at least 3.',
        'total_questions.max' => 'The total number of questions must not be greater than 30.',
    ]);

    session()->forget(['quiz', 'total_questions', 'current_question', 'correct_answers', 'wrong_answers']); // Zera a sessão antes de iniciar

    $total_questions = intval($request->input('total_questions'));
    $quiz = $this->prepareQuiz($total_questions);

    session()->put([
        'quiz' => $quiz,
        'total_questions' => $total_questions,
        'current_question' => 0, // Começa corretamente do zero
        'correct_answers' => 0,
        'wrong_answers' => 0,
    ]);

    return redirect()->route('game');
}

    private function prepareQuiz(int $total_questions): array
    {
        $questions = [];
        $total_countries = count($this->app_data);

        if ($total_countries < $total_questions) {
            abort(400, 'Not enough countries available.');
        }

        $indexes = array_rand($this->app_data, $total_questions);

        foreach ((array) $indexes as $index) {
            $question = [];
            $question['country'] = $this->app_data[$index]['country'];
            $question['correct_answer'] = $this->app_data[$index]['capital'];

            $other_capitals = array_column($this->app_data, 'capital');
            $other_capitals = array_diff($other_capitals, [$question['correct_answer']]);
            shuffle($other_capitals);

            $question['wrong_answers'] = array_slice($other_capitals, 0, 3);
            $questions[] = $question;
        }

        return $questions;
    }

    public function game(): View
    {
        $quiz = session('quiz', []);
        $total_questions = session('total_questions', 0);
        $current_question = session()->has('current_question') ? session('current_question') : 0;

        if (!isset($quiz[$current_question])) {
            return redirect()->route('startGame'); // Redireciona caso algo esteja errado
        }

        $answers = $quiz[$current_question]['wrong_answers'];
        $answers[] = $quiz[$current_question]['correct_answer'];
        shuffle($answers);

        return view('game', [
            'country' => $quiz[$current_question]['country'],
            'totalQuestions' => $total_questions,
            'currentQuestion' => $current_question, // or $current_question + 1
            'answers' => $answers,
        ]);
    }

    public function answer($enc_answer)
    {
        try {
            $answer = Crypt::decryptString($enc_answer);
        } catch (\Exception $e) {
            return redirect()->route('game');
        }

        $quiz = session('quiz');
        $current_question = session('current_question');
        $correct_answer = $quiz[$current_question]['correct_answer'];
        $correct_answers = session('correct_answers');
        $wrong_answers = session('wrong_answers');

        if ($answer === $correct_answer) {
            $correct_answers++;
            $quiz[$current_question]['correct'] = true;
        } else {
            $wrong_answers++;
            $quiz[$current_question]['correct'] = false;
        }

        session()->put([
            'quiz' => $quiz,
            'correct_answers' => $correct_answers,
            'wrong_answers' => $wrong_answers,
        ]);

        $data = [
            'country' => $quiz[$current_question]['country'],
            'correct_answer' => $correct_answer,
            'choice_answer' => $answer,
            'currentQuestion' => $current_question,
            'totalQuestions' => session('total_questions'),
        ];

        return view('answer_result')->with($data);
    }
}
