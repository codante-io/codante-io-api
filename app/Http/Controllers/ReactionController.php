<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReactionController extends Controller
{

    public function store()
    {
        $data = request()->validate([
            "reactable_id" => "required",
            "reactable_type" => "required",
            "reaction" => "required",
        ]);

        $reactableClass = "App\\Models\\" . $data["reactable_type"];

        // check if reactable model exists, if not, return error
        if (!class_exists($reactableClass)) {
            return response()->json(
                [
                    "message" => "Reactable model does not exist",
                ],
                404
            );
        }

        // check if the model is reactable
        if (!in_array("App\\Traits\\Reactable", class_uses($reactableClass))) {
            return response()->json(
                [
                    "message" => "Model is not reactable",
                ],
                404
            );
        }

        // find if the reactable model exists
        $reactable = $reactableClass::findOrFail($data["reactable_id"]);

        // if the user has already reacted, delete the reaction
        if ($reactable->isReactedBy($data['reaction'], auth()->user())) {
            $reactable->removeReaction($data["reaction"], auth()->user());
            return response()->json([
                "message" => "Reaction removed successfully",
                "result" => 'destroy',
                "reaction" => $data["reaction"]
            ]);
        }

        // create the reaction
        $reactable->react($data["reaction"], auth()->user());

        return response()->json([
            "message" => "Reaction created successfully",
            "result" => 'create',
            "reaction" => $data["reaction"]
        ]);
    }
}
