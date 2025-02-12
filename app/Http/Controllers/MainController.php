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

        // $quiz = $this->prepareQuiz($total_questions);

        // dd($quiz);
    }
}
