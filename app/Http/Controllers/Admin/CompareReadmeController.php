<?php

namespace App\Http\Controllers\Admin;

use App\Services\CompareChallengeReadmes;

class CompareReadmeController
{
    public function test(string $slug)
    {
        (new CompareChallengeReadmes())->testChallengeDescription($slug);
    }

    public function compare(string $slug)
    {
        (new CompareChallengeReadmes())->getDiffHtml($slug);
    }
}
