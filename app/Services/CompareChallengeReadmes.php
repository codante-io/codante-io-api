<?php

namespace App\Services;

use App\Models\Challenge;
use App\Notifications\Discord;
use Mistralys\Diff\Diff;

class CompareChallengeReadmes
{
    public function checkAll()
    {
        $challenges = Challenge::where("status", "published")->get("slug");

        // Discord Message
        new Discord(
            "==== 🔍 Iniciando comparação de Readmes de todos os Mini Projetos... ====",
            "notificacoes-site"
        );

        foreach ($challenges as $challenge) {
            $this->testChallengeDescription($challenge->slug);
            // wait 1 seconds to avoid rate limit
            sleep(1);
        }

        // Discord Message
        new Discord(
            "==== 🎉 Finalizada comparação de Readmes de todos os Mini Projetos.",
            "notificacoes-site"
        );
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
            $diffUrl =
                config("app.url") . "/admin/compare-readmes/$challengeSlug";

            foreach ($diff as $line) {
                if ($line[1] !== 0) {
                    // Log a message or throw an exception if the descriptions are different
                    new Discord(
                        "❌ Mini Projeto **$challenge->slug**: diferença nos Readmes. Diff: $diffUrl",
                        "notificacoes-site"
                    );
                    $isEqual = false;
                    break;
                }
            }

            if ($isEqual) {
                new Discord(
                    "✅ Mini Projeto **$challenge->slug**: Readmes iguais.",
                    "notificacoes-site"
                );
            }
        }
    }
}
