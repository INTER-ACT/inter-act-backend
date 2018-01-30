<?php
/**
 * Created by PhpStorm.
 * User: danube
 * Date: 30.01.18
 * Time: 16:04
 */

namespace App\Domain\Manipulators;


use App\Exceptions\CustomExceptions\InternalServerError;
use App\Http\Resources\SuccessfulCreationResource;
use App\Reports\Report;
use App\User;

class ReportManipulator
{
    /**
     * @param User $user
     * @param array $data
     * @return SuccessfulCreationResource
     * @throws InternalServerError
     */
    public static function create(User $user, array $data) : SuccessfulCreationResource
    {
        $report = Report::where([['user_id', '=', $user->id], ['reportable_id', '=', $data['reportable_id']], ['reportable_type', '=', $data['reportable_type']]])->first();
        if(empty($report))
            $report = new Report();
        $report->fill($data);
        $report->user_id = $user->id;
        if(!$report->save())
            throw new InternalServerError("Could not create a Report with the given data.");
        return new SuccessfulCreationResource($report);
    }
}