<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MainController extends Controller
{
    private $app_data;

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
        ],
        [
            'total_questions.required' => 'Please enter the total number of questions.',
            'total_questions.integer' => 'The total number of questions must be an integer.',
            'total_questions.min' => 'The total number of questions must be at least 3.',
            'total_questions.max' => 'The total number of questions must not be greater than 30.',
        ]
    );

        $total_questions = intval($request->input('total_questions'));

        $quiz = $this->prepareQuiz($total_questions);

        dd($quiz);
    }

    private function prepareQuiz($total_questions)
    {
        $questions = [];
        $total_countries = count($this->app_data);

        $indexes = range(0, $total_countries - 1);
        shuffle($indexes);
        $indexes = array_slice($indexes, 0, $total_questions);

        $question_number = 1;
        foreach ($indexes as $index) {
            $questions['question_number'] = $question_number;
            $question['country'] = $this->app_data[$index]['country'];
            $question['correct_answer'] = $this->app_data[$index]['capital'];

            $other_capitals = array_column($this->app_data, 'capital');

            $other_capitals = array_diff($other_capitals, [$question['correct_answer']]);

            shuffle($other_capitals);
            $question['wrong_answers'] = array_slice($other_capitals, 0, 3);

            $question['correct'] = null;

            $questions[] = $question;
        }

        return $questions;
    }
}
