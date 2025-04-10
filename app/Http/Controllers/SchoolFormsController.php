<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SchoolFormsController extends Controller
{
    public function generate_form_137(Request $request)
    {
        $student_grades = $request->student_grades;
        try {
            $filename = $request->lrn . '-form-10.xlsx';
            $relativePath = 'school-forms/' . $filename;
            $filePath = storage_path('app/school-forms/form137.xlsx');
            $newPath = storage_path('app/school-forms/'. $filename);

            if (Storage::exists($relativePath)) {
                Storage::delete($relativePath);
            }

            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();

            //LAST NAME
            $sheet->setCellValue('G7', $request->last_name);

            //FIRST NAME
            $sheet->setCellValue('W7', $request->first_name);

            //EXTENSION NAME
            $sheet->setCellValue('AN7', $request->extension);

            //LRN
            $sheet->setCellValue('M8', $request->lrn);

            //BIRTHDAY
            $sheet->setCellValue('AH8', $request->birthday);

            //SEX
            $sheet->setCellValue('AV8', $request->sex);

            foreach($student_grades as $index => $student_grade){
                $sheet->setCellValue('B' . (25 + $index + 1), $student_grade['title']);
                $sheet->setCellValue('U' . (25 + $index + 1), $student_grade[1]);
                $sheet->setCellValue('Y' . (25 + $index + 1), $student_grade[1]);
                $sheet->setCellValue('AC' . (25 + $index + 1), $student_grade[1]);
                $sheet->setCellValue('AG' . (25 + $index + 1), $student_grade[1]);
                $sheet->setCellValue('AJ' . (25 + $index + 1), $student_grade["final_rating"]);
            }

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($newPath);
            
            $url = asset('public/forms/' . $filename);
            Log::info($url);
            return $url;
        } catch (\Throwable $th) {
            Log::info($th);
        }
    }
}
