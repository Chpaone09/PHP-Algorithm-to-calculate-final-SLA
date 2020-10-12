<?php



/*
***********************************************************

Topic   : Algorithm to calculate final SLA using php.
Author  : Chpaone09®
Date    : Oct 12, 2020

************************************************************
*/



public static function getFinalSLA($startTime, $processTime){

					$total_hours = 0;

					$final_sla='';

					$remainingHours =0;

					$remainingMinutes=0;

					$remainingDays=0;

					$pre_hrs = array('18 PM', '19 PM','20 PM', '21 PM', '22 PM', '23 PM');

					$post_hrs= array('00 AM', '01 AM', '02 AM', '03 AM', '04 AM', '05 AM', '06 AM', '07 AM');

					$hrs = date("H A", strtotime($startTime));

					// adjusting start date

					if(in_array($hrs, $pre_hrs)){

									$startTime = date("Y-m-d 08:00:00", strtotime($startTime . " +1 day"));

					}elseif(in_array($hrs, $post_hrs)){

									$startTime = date("Y-m-d 08:00:00", strtotime($startTime));

					}             

				   // echo $processTime;die;

					do

					{

						$startTime = self::checkHolidays($startTime);

						$day = self::dayByNumber($startTime);                   

					}while(($day != 'Sat') && ($day != 'Sun') && (in_array(date("Y-m-d", strtotime($startTime)), self::getHolidays())));

					// echo 'New Start Time -> '.$startTime.'<br>';

					$currentDayEnd = date("Y-m-d 18:00:00", strtotime($startTime));


// ======================  Start Working Here ================================================================//

					$date1=date_create($startTime);  

					$date2=date_create($currentDayEnd);

					$firstDayElapsedTime=date_diff($date1,$date2);
					
					// echo "<pre>";print_r($firstDayElapsedTime);die;

	 

					if (preg_match('/day/', $processTime)) {

						$total_hours = (substr($processTime, 0, 2)) * 13;

						if(($firstDayElapsedTime->h)>0){
							$remainingHours = $total_hours - ($firstDayElapsedTime->h);
						}else{
							$elapsed = round((($firstDayElapsedTime->i)/60),2);
							$remainingHours = (int)($total_hours - $elapsed);
						}

						if(($firstDayElapsedTime->i)>0){
							$remainingMinutes = 60 - ($firstDayElapsedTime->i);
						}
						else{
							$remainingMinutes = ($firstDayElapsedTime->i);
						}             

						if($remainingMinutes > 1){

							$remainingHours = $remainingHours - 1 ;

							if($remainingHours<0){

											$remainingHours =0;

							}

						}

						}
					
					elseif (preg_match('/hour/', $processTime)){

						$total_hours = (int)(substr($processTime, 0, 2));
						
						// echo $total_hours;die;
						// echo $total_hours;die;
						// echo ((int)$firstDayElapsedTime->h - (int)$total_hours);die;

						if((($firstDayElapsedTime->h)- $total_hours)>=0){

						$final_sla = date("Y-m-d H:i:s", strtotime('+' . $total_hours . ' hours ', strtotime($startTime)));     

						}else{

							$remainingHours = ($total_hours - ($firstDayElapsedTime->h));

							$remainingMinutes = (60 - ($firstDayElapsedTime->i));

							if($remainingMinutes > 1){

								$remainingHours = $remainingHours - 1 ;

								if($remainingHours<0){

									$remainingHours =0;
								}
							}
						}
					}else{

									// echo "Invalid Time";

					}

					$remainingTime = $remainingHours + (($remainingMinutes)/60);

					if ($remainingTime <= 0){
						$final_sla = date("Y-m-d H:i:s", strtotime('+'.($total_hours) . ' hour ' . $remainingMinutes . ' minutes', strtotime($startTime)));
					}

					elseif ($remainingTime >= 13) {
						$remainingDays = (int)($remainingHours / 13);
						$remainingHours = (int)(($remainingTime - ($remainingDays * 13))-1);

						if($remainingHours<0){
							$remainingHours =0;
						}                             

						if($remainingDays <= 0){
							$final_sla = date("Y-m-d 08:00:00", strtotime($startTime . " +1 day"));
							$final_sla = date("Y-m-d H:i:s", strtotime('+' . $remainingHours . ' hours ' . round($remainingMinutes*60) . ' minutes', strtotime($final_sla)));
						}
						else{
							$final_sla = date("Y-m-d 08:00:00", strtotime($startTime . " +" . ($remainingDays) . " day"));
							$final_sla = date("Y-m-d H:i:s", strtotime('+' . $remainingHours . ' hours ' . $remainingMinutes . ' minutes', strtotime($final_sla)));
						}

						}else{
							$final_sla = date("Y-m-d 08:00:00", strtotime($startTime . " +" . ($remainingDays) . " day"));
							$final_sla = date("Y-m-d H:i:s", strtotime('+' . $remainingHours . ' hours ' . $remainingMinutes . ' minutes', strtotime($final_sla)));
						}

				   

					// checking for Sat, Sun & Holidays in between --

						$datesArray = self::getDatesFromRange($startTime, $final_sla);

						$daysToAdd=0;

						if(!empty($datesArray)){
							if (count($datesArray)>1) {
								$dayArray[] = array();
								//echo count($datesArray);

								for ($i = 0; $i < (count($datesArray)); $i++) {
									$dayArray[$i] = $datesArray[$i];
								}

							}
							else{
								$dayArray[] = $datesArray[0];
							}             

							foreach ($dayArray as $day) {
								$weekOfTheDay[] = date_create($day)->format('D');
							}

							foreach ($dayArray as $day1) {
								$day = date_create($day1)->format('D');
								if ($day == "Sat") {
									$daysToAdd = $daysToAdd + 1;
								}

								if ($day == "Sun") {
									$daysToAdd = $daysToAdd + 1;
								}

								if (in_array(date("Y-m-d", strtotime($day1)), self::getHolidays())) {
									$daysToAdd = $daysToAdd + 1;
								}                                             	
							}
					}

					$midHoursToAdd = date_create($final_sla)->format('H');
					$midMinutesToAdd = date_create($final_sla)->format('i');

					if($remainingDays >= 1 && $daysToAdd >0){
						$flag = 0;
						$stop=0;
						$hoursToAdd = $daysToAdd * 13;
						$daysToAdd = $hoursToAdd/13;
						$final_sla = date("Y-m-d H:i:s", strtotime($final_sla . " +1 day"));
						$day = self::dayByNumber($final_sla);
						while(($stop != 1) || ($daysToAdd)!=0){               
							if($daysToAdd > 0 ){

								$day = self::dayByNumber($final_sla);

								if($day == 'Sat'){
									$final_sla = date("Y-m-d H:i:s", strtotime($final_sla . " +1 day"));
								}
								elseif($day == 'Sun'){
									$final_sla = date("Y-m-d H:i:s", strtotime($final_sla . " +1 day"));
								}
								elseif (in_array(date("Y-m-d", strtotime($final_sla)), self::getHolidays())) {
									$final_sla = date("Y-m-d H:i:s", strtotime($final_sla . " +1 day"));
								}
								else{
									$final_sla = date("Y-m-d H:i:s", strtotime($final_sla . " +1 day"));
									$daysToAdd--;
									if($daysToAdd == 0){
									}
								}
							}
							else{
								$final_sla = date("Y-m-d H:i:s", strtotime($final_sla . " -1 day"));
								$stop = 1;
							}
						}

						$day = self::dayByNumber($final_sla);
						if(  ( ($day != 'Sat') && ($day != 'Sun') && (!in_array(date("Y-m-d", strtotime($final_sla)), self::getHolidays())))  ){
							$final_sla = date("Y-m-d 00:00:00", strtotime($final_sla . " +1 day"));
							$final_sla = date("Y-m-d H:i:s", strtotime('+' . $midHoursToAdd . ' hours ' . $midMinutesToAdd . ' minutes', strtotime($final_sla)));

						}

					}
					else{
						if($remainingHours > 0 || $remainingMinutes > 0 || $remainingTime>0){
							$day = self::dayByNumber($final_sla);
							if((($day != 'Sat') && ($day != 'Sun') && (!in_array(date("Y-m-d", strtotime($final_sla)), self::getHolidays())))){
								$final_sla = date("Y-m-d 00:00:00", strtotime($final_sla . " +1 day"));
								$final_sla = date("Y-m-d H:i:s", strtotime('+' . $midHoursToAdd . ' hours ' . $midMinutesToAdd . ' minutes', strtotime($final_sla)));
							}
						}
					}

					// checking for Sat , Sun and holidays at Last

					do
					{
						$final_sla = self::checkHolidaysAtEnd($final_sla);
						$day = self::dayByNumber($final_sla);                                      
					}while(($day != 'Sat') && ($day != 'Sun') && (in_array(date("Y-m-d", strtotime($final_sla)), self::getHolidays())));
				   
					$hrs = date("H A", strtotime($final_sla));

					if(in_array($hrs, $pre_hrs) || in_array($hrs, $post_hrs)){
						$secondLastDayEnd = date("Y-m-d 18:00:00", strtotime($final_sla));
						$date1=date_create($secondLastDayEnd);
						$date2=date_create($final_sla);
						$FinalRemainingTimeForLastDay=date_diff($date1,$date2);
						$final_sla = date("Y-m-d 08:00:00", strtotime($final_sla . " +1 day"));
						$final_sla = date("Y-m-d H:i:s", strtotime('+' . ($FinalRemainingTimeForLastDay->h) . ' hours ' . ($FinalRemainingTimeForLastDay->i) . ' minutes', strtotime($final_sla)));
						}

					// post sat , sun & holiday check --
					do
					{
						$final_sla = self::checkHolidaysAtEnd($final_sla);
						$day = self::dayByNumber($final_sla);                                      
					}while(($day != 'Sat') && ($day != 'Sun') && (in_array(date("Y-m-d", strtotime($final_sla)), self::getHolidays())));
	
	return $final_sla;
	}





?>
