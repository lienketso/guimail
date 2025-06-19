<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PlagiarismController extends Controller
{
    public function index()
    {
        return view('check-form');
    }

    public function check(Request $request)
    {
        $text = $request->input('text');
        if (!$text) return back()->withErrors(['text' => 'Vui lòng nhập nội dung']);

        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        $totalScore = 0;
        $matchScore = 0;
        $results = [];
        $sources = [];

        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (!$sentence) continue;

            $score = strlen($sentence);
            $totalScore += $score;

            $cleaned = $this->cleanText($sentence);
            $response = $this->searchGoogle($cleaned);

            $isMatch = false;
            $matchUrls = [];

            foreach ($response['items'] ?? [] as $item) {
                $snippet = $this->cleanText($item['snippet'] ?? '');
                if ($this->isSimilar($cleaned, $snippet, 70)) {
                    $isMatch = true;
                    $matchUrls[] = $item['link'];
                    $sources[] = $item['link'];
                }
            }

            if ($isMatch) $matchScore += $score;

            $results[] = [
                'sentence' => $sentence,
                'match' => $isMatch,
                'urls' => $matchUrls,
            ];
        }

        $plagiarismPercent = $totalScore > 0 ? round(($matchScore / $totalScore) * 100) : 0;
        $sources = array_unique($sources);

        return view('check-result', [
            'results' => $results,
            'original' => $text,
            'percent' => $plagiarismPercent,
            'sources' => $sources,
        ]);
    }

    private function cleanText($text)
    {
        $text = strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text); // remove punctuation
        $words = explode(' ', $text);
        $stopwords = ['the', 'a', 'an', 'is', 'are', 'was', 'were', 'and', 'or', 'to', 'in', 'of', 'that', 'with'];
        $filtered = array_diff($words, $stopwords);
        return implode(' ', $filtered);
    }

    private function isSimilar($original, $snippet, $threshold = 70)
    {
        similar_text($original, $snippet, $percent);
        return $percent >= $threshold;
    }

    private function searchGoogle($query)
    {
        $apiKey ='AIzaSyBZe2nxpQa57HmUMEQ-83ml7bAs09a6NBs';
        $cx = 'c1ad8a992919b49ad';

        $response = Http::get('https://www.googleapis.com/customsearch/v1', [
            'key' => $apiKey,
            'cx' => $cx,
            'q' => '"' . $query . '"', // tìm chính xác cụm từ
        ]);

        return $response->successful() ? $response->json() : [];
    }
    private function checkWithGoogle($query)
    {
        $apiKey = 'AIzaSyBZe2nxpQa57HmUMEQ-83ml7bAs09a6NBs';
        $cx = 'c1ad8a992919b49ad';

        $response = Http::get('https://www.googleapis.com/customsearch/v1', [
            'key' => $apiKey,
            'cx' => $cx,
            'q' => $query,
        ]);

        if ($response->successful() && isset($response['items'])) {
            $count = count($response['items']);
            return "Có thể đạo văn ($count kết quả)";
        } elseif ($response->successful()) {
            return "Khó phát hiện (0 kết quả)";
        } else {
            return "Lỗi API: " . $response->body();
        }
    }

}
