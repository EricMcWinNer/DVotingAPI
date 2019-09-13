<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidDateException;
use Closure;

class ElectionValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $electionName = $request->input('name');
            $startDate = Carbon::createFromFormat('D M d Y H:i:s e+', $request->input('start_date'))->setTimezone("UTC");
            $endDate = Carbon::createFromFormat('D M d Y H:i:s e+', $request->input('end_date'))->setTimeZone("UTC");
            if (count(explode(" ", $electionName)) < 3)
                return response(["isValid" => false, "field" => "electionName"]);
            else if (empty($request->start_date))
                return response(["isValid" => false, "field" => "startDate"]);
            else if (empty($request->end_date))
                return response(["isValid" => false, "field" => "endDate"]);
            else if ($startDate->setTimezone("+01:00")->lessThan(Carbon::now("+01:00")->addHour()))
                return response(["isValid" => false, "field" => "smallStartDate"]);
            /*else if ((int)$startDate->format('i') !== 0 && (int)$startDate->format('i') % 10 !== 0)
                return response(["isValid" => false, "field" => "startDateNotTens"]);
            else if ((int)$endDate->format('i') !== 0 && (int)$endDate->format('i') % 10 !== 0)
                return response(["isValid" => false, "field" => "endDateNotTens"]);*/
            else if ($endDate->lessThan($startDate->addHour()))
                return response(["isValid" => false, "field" => "smallEndDate"]);
            else if ($endDate->lessThanOrEqualTo(Carbon::now()))
                return response(["isValid" => false, "field" => "pastEndDate"]);
            else
                return $next($request);
        } catch (InvalidDateException $e) {
            return response(["isValid" => false, "field" => "invalidDates"]);
        } catch(\InvalidArgumentException $e)
        {
            return response(["isValid" => false, "field" => "invalidDates"]);
        }
    }
}
