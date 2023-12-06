<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\Discord;
use Mistralys\Diff\Diff;

class CompareChallengeReadmes
{
    public function checkAll()
    {
        $challenges = Challenge::where("status", "published")->get("slug");

        foreach ($challenges as $challenge) {
            $this->testChallengeDescription($challenge->slug);
            // wait 1 second between each request
            sleep(1);
        }
    }

    public static function getDiffHtml(string $slug)
    {
        // first we get the challenge description from the database
        $challenge = Challenge::select("id", "description", "repository_name")
            ->where("slug", $slug)
            ->first();

        if (!$challenge) {
            return [
                "style" => Diff::createStyler()->getStyleTag(),
                "html" => "Challenge not found",
            ];
        }

        $challengeDescription = $challenge->description;
        $repositoryName = $challenge->repository_name;

        // then we get the challenge description from the readme file in the repository
        $challengeReadme = file_get_contents(
            "https://raw.githubusercontent.com/codante-io/$repositoryName/main/README.md"
        );

        // now we remove the h1 title from the readme
        $challengeReadme = preg_replace("/^#[^#].*$/m", "", $challengeReadme);

        // now we trim the first and last line of the readme and description
        $challengeReadme = trim($challengeReadme);
        $challengeDescription = trim($challengeDescription);

        // now we diff both files
        $diff = Diff::compareStrings($challengeReadme, $challengeDescription);

        echo Diff::createStyler()->getStyleTag();
        echo $diff->toHTML();
    }

    public function testChallengeDescription($challengeSlug)
    {
        $challenge = Challenge::where("slug", $challengeSlug)->first();

        if ($challenge) {
            $repositoryName = $challenge->repository_name;

            $challengeReadme = file_get_contents(
                "https://raw.githubusercontent.com/codante-io/$repositoryName/main/README.md"
            );

            // Remove the h1 title from the readme
            $challengeReadme = preg_replace(
                "/^#[^#].*$/m",
                "",
                $challengeReadme
            );

            // Trim the first and last line of the readme and description
            $challengeReadme = trim($challengeReadme);
            $challengeDescription = trim($challenge->description);

            // Diff both files
            $diff = Diff::compareStrings(
                $challengeReadme,
                $challengeDescription
            )->toArray();

            $isEqual = true;
            $appURL =
                config("app.url") . "/admin/compare-readmes/$challengeSlug";

            foreach ($diff as $line) {
                if ($line[1] !== 0) {
                    // Log a message or throw an exception if the descriptions are different
                    new Discord(
                        "A descrição do desafio $challenge->slug é diferente do README do GitHub. 😞 - Acesse $appURL para ver a diff completa",
                        "notificacoes-site"
                    );
                    $isEqual = false;
                    break;
                }
            }

            if ($isEqual) {
                new Discord(
                    "O MP $challenge->slug está com o Readme igual ao do Github 🎉",
                    "notificacoes-site"
                );
            }
        } else {
            // Log::warning("Challenge with slug $challengeSlug not found.");
        }
    }
}
