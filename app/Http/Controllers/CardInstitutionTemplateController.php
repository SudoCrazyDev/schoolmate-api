<?php

namespace App\Http\Controllers;

use App\Models\CardInstitutionTemplate;
use App\Models\InstitutionSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CardInstitutionTemplateController extends Controller
{
    public function get_institutions_card_templates($institution_id)
    {
        return CardInstitutionTemplate::where('institution_id', $institution_id)->get();
    }

    public function get_card_template($card_template_id)
    {
        return CardInstitutionTemplate::findOrFail($card_template_id);
    }

    public function add_card_template(Request $request)
    {
        try {
            CardInstitutionTemplate::create([
                'institution_id' => $request->institution_id,
                'title' => $request->title ? $request->title : 'No Title Card Template',
                'subjects' => $request->subjects
            ]);
            return response()->json([
                'message' => 'Card Created Successfully'
            ], 200);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'message' => 'Card was not created'
            ], 400);
        }
    }
    
    public function update_card_template(Request $request)
    {
        $card_template = CardInstitutionTemplate::where('id', $request->card_template_id)->first();
        if(!$card_template) return response()->json(['message' => 'Card Template not Found!'], 404);
        try {
            $card_template->title = $request->title;
            $card_template->subjects = $request->subjects;
            $card_template->save();
            return response()->json(['message' => 'Card Template Updated'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Card Template was not updated'], 400);
        }
    }
    
    public function update_section_card_template(Request $request)
    {
        try {
            $institution_section = InstitutionSection::where('id', $request->section_id)->first();
            if(!$institution_section) return response()->json(['message' => 'No Section Found!'], 400);
            $institution_section->card_template_id = $request->card_template_id;
            $institution_section->save();
            return response()->json(['message' => 'Section Card Template Updated!'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Update Failed!'], 400);
        }
    }
}
