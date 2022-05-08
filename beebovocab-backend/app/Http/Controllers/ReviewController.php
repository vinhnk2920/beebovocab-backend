<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function countLevel(Request $request)
    {
        $user_id = $request->input('user_id');
        if (is_null($user_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng không tồn tại'
            ]);
        }

        $level1 = DB::table('learning_results')->where('user_id', $user_id)->where('level', '1')->count();
        $level2 = DB::table('learning_results')->where('user_id', $user_id)->where('level', '2')->count();
        $level3 = DB::table('learning_results')->where('user_id', $user_id)->where('level', '3')->count();
        $level4 = DB::table('learning_results')->where('user_id', $user_id)->where('level', '4')->count();
        $level5 = DB::table('learning_results')->where('user_id', $user_id)->where('level', '5')->count();
        return response()->json([
            'success' => true,
            'message' => 'Thành công!!!',
            'data' => [
                'level1' => $level1,
                'level2' => $level2,
                'level3' => $level3,
                'level4' => $level4,
                'level5' => $level5,
            ],
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showVocab(Request $request)
    {
        $user_id = $request->input('user_id');

        if (is_null($user_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng không tồn tại'
            ]);
        }
        $vocabs = DB::table('learning_results')->where('user_id', $user_id)->join('vocabularies', 'vocabularies.id', '=', 'learning_results.vocabulary_id' )->join('related_question', 'learning_results.vocabulary_id', '=', 'related_question.vocabulary_id')->select('learning_results.*', 'vocabularies.word' ,'related_question.question')->get();
        if (empty($vocabs)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có dữ liệu'
            ]);
        }
        $newVocabList = [];
        foreach ($vocabs->toArray() as $vocab) {
            $now = date('Y-m-d H:i:s');
            $start_time = Carbon::parse($now);
            $finish_time = Carbon::parse($vocab->updated_at);
            $lengthHours = $start_time->diffInHours($finish_time);
            $vocab->hours = $lengthHours;
            if($vocab->level === 1 && $lengthHours>=1) {
                array_push($newVocabList, $vocab);
            } else if ($vocab->level === 2 && $lengthHours>=6) {
                array_push($newVocabList, $vocab);
            } else if ($vocab->level === 3 && $lengthHours>=24*2) {
                array_push($newVocabList, $vocab);
            } else if ($vocab->level === 4 && $lengthHours>=24*5) {
                array_push($newVocabList, $vocab);
            } else if ($vocab->level === 4 && $lengthHours>=24*20) {
                array_push($newVocabList, $vocab);
            }
        }
        $reviewList = [];
        foreach ($newVocabList as $vocab) {
            $dot = str_repeat('_ ', strlen($vocab->word));
            $vocab->test = str_replace($vocab->word, $dot, $vocab->question);
            array_push($reviewList, $vocab);
        }
        return response()->json([
            'success' => true,
            'message' => 'Thành công!!!',
            'data' => [
                'vocab' => $reviewList,
                'count' => count($newVocabList)
            ],
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function latelyReviewSets(Request $request)
    {
        $user_id = $request->input('user_id');
        if (empty($user_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần nhập id của user'
            ]);
        }
        $vocabulary_sets = DB::table('learning_results')->where('user_id', $user_id)->join('vocabularies', 'vocabularies.id', '=', 'learning_results.vocabulary_id')->join('vocabulary_sets', 'vocabulary_sets.id', '=', 'vocabularies.vocabulary_set_id')->select('learning_results.updated_at', 'vocabularies.vocabulary_set_id', 'vocabulary_sets.*')->get()->unique('id')->toArray();
        if (empty($vocabulary_sets)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có dữ liệu'
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Thành công',
            'data' =>  $vocabulary_sets
        ]);
    }
}
