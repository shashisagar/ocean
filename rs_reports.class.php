<?php

class RS_REPORT_Manager
{
    public static function getRequestByConsumerReport($db_config,$from_date,$to_date)
    {
        require_once( "databaseutilities.class.php" );
        $db = new DatabaseUtilities( $db_config );
        $from_date_final="";
        $to_date_final="";
        $result=array();

        if(!empty($from_date) && !empty($to_date)) {

            $from_date_1 = explode('-', $from_date);
            $from_date_m = $from_date_1[0];
            $from_date_y = $from_date_1[1];
            $to_date_1 = explode('-', $to_date);
            $to_date_y = $to_date_1[1];
            $to_date_m = $to_date_1[0];

            $from_date_final = $from_date_y . "-" . $from_date_m;
            $to_date_final = $to_date_y . "-" . $to_date_m;
            $query = "SELECT USER_ID,COUNT(REQUEST_ID) as count_1 From RS_CART_REQUEST where DATE_FORMAT(DATE_CREATED,'%Y-%m') between '$from_date_final' and '$to_date_final' group by USER_ID";

            $resultDB = $db->execute_query($query);

        }
        else{
            $query = "SELECT USER_ID,COUNT(REQUEST_ID) as count_1 From RS_CART_REQUEST group by USER_ID";
            $resultDB = $db->execute_query($query);
        }

        $final_result=array();
        while( $row = mysqli_fetch_array($resultDB, MYSQL_ASSOC))
        {
            $USER_ID=$row['USER_ID'];
            $result['USER_ID']=$USER_ID;
            $result['TOTAL_REQUEST']=$row['count_1'];


            if(!empty($from_date_final) && !empty($to_date_final)) {

                $query_1 = "SELECT SUM(QTY) as c_count FROM `RS_CART_COMPONENT` where USER_ID=$USER_ID and DATE_FORMAT(DATE(CREATED_DATE),'%Y-%m') between '$from_date_final' and '$to_date_final'";
                $resultDB_1 = $db->execute_query($query_1);
            }
            else{
                $query_1 = "SELECT SUM(QTY) as c_count FROM `RS_CART_COMPONENT` where USER_ID=$USER_ID";
                $resultDB_1 = $db->execute_query($query_1);
            }

            $count_cmp_1=0;

            if( $row = mysqli_fetch_array($resultDB_1, MYSQL_ASSOC))
            {
                $count_cmp_1=$row['c_count'];

            }

            $total_count=$count_cmp_1;
            $result['TOTAL_COMPONENT']=$total_count;

            $query_3="SELECT NICK_NAME FROM `USERS` where ID=$USER_ID";
            $resultDB_3 = $db->execute_query($query_3);

            if( $row = mysqli_fetch_array($resultDB_3, MYSQL_ASSOC))
            {
                $result['USERNAME']=$row['NICK_NAME'];
            }
            $final_result[]=$result;
        }

        function sortByTotalRequest($a, $b)
        {
            $a = $a['TOTAL_COMPONENT'];
            $b = $b['TOTAL_COMPONENT'];

            if ($a == $b) return 0;
            return ($a > $b) ? -1 : 1;
        }
        usort($final_result, 'sortByTotalRequest');

        return $final_result;
    }
    public static function getRequestTimeline($db_config, $from_date, $to_date)
    {
        require_once("databaseutilities.class.php");
        $db = new DatabaseUtilities($db_config);
        $result = array();
        if (!empty($from_date) && !empty($to_date)) {
            $from_date_1 = explode('-', $from_date);
            $from_date_m = $from_date_1[0];
            $from_date_y = $from_date_1[1];
            $to_date_1 = explode('-', $to_date);
            $to_date_y = $to_date_1[1];
            $to_date_m = $to_date_1[0];
            $from_date_final = $from_date_y . "-" . $from_date_m;
            $to_date_final = $to_date_y . "-" . $to_date_m;
            $query = "SELECT * From RS_CART_REQUEST where DATE_FORMAT(DATE_CREATED,'%Y-%m') between '$from_date_final' and '$to_date_final' and STATUS IN ('Received','Resolved')";
            $resultDB = $db->execute_query($query);
        } else {
            $query = "SELECT * From RS_CART_REQUEST where STATUS IN ('Received','Resolved')";
            $resultDB = $db->execute_query($query);
        }
        $final_array=array();
        while ($row = mysqli_fetch_array($resultDB, MYSQL_ASSOC)) {
            $result['REQUEST_ID'] = $row['REQUEST_ID'];
            $result['STATUS'] = $row['STATUS'];
            $result['UPDATED_DATE'] = $row['UPDATED_DATE'];
            $final_array[]=$result;
        }

        return $final_array;
    }
    public static function getStatusByPlatform($db_config, $from_date, $to_date)
    {
        require_once("databaseutilities.class.php");
        $db = new DatabaseUtilities($db_config);
        $query = "SELECT PRODUCT_PLATFORM From PRODUCTS GROUP BY PRODUCT_PLATFORM";
        $resultDB = $db->execute_query($query);

        $result = array();
        $final_result = array();

        $from_date_final="";
        $to_date_final="";

        if(!empty($from_date) && !empty($to_date)) {
            $from_date_1 = explode('-', $from_date);
            $from_date_m = $from_date_1[0];
            $from_date_y = $from_date_1[1];
            $to_date_1 = explode('-', $to_date);
            $to_date_y = $to_date_1[1];
            $to_date_m = $to_date_1[0];

            $from_date_final = $from_date_y . "-" . $from_date_m;
            $to_date_final = $to_date_y . "-" . $to_date_m;
        }

        while ($row = mysqli_fetch_array($resultDB, MYSQL_ASSOC)) {
            $p_platform = $row['PRODUCT_PLATFORM'];
            $Created = 0;
            $Finalized = 0;
            $Received = 0;
            $On_hold = 0;
            $query_11 = "select sp.NAME as NAME from PRODUCTS P1,
SOLICITS_PRODUCTS sp
where sp.ID=P1.PRODUCT_PLATFORM 
        and sp.ID=$p_platform";

            $resultDB_11 = $db->execute_query($query_11);

            if ($row = mysqli_fetch_array($resultDB_11, MYSQL_ASSOC)) {
                $result['NAME'] = $row['NAME'];
            }

            $query_1 = "SELECT * From PRODUCTS where PRODUCT_PLATFORM=$p_platform";
            $resultDB_1 = $db->execute_query($query_1);

            while ($row = mysqli_fetch_array($resultDB_1, MYSQL_ASSOC)) {
//                $final_result_1 = array();
                $sku = $row['SKU'];

                $query_final_1 = "SELECT * From RS_CART_COMPONENT where PRODUCT_ID=$sku";
                $result_final_1 = $db->execute_query($query_final_1);
                $count_1 = $result_final_1->num_rows;

                if ($count_1 >0) {

                    if (!empty($from_date) && !empty($to_date)) {
                        $query_11 = "select r.STATUS from RS_CART_REQUEST r,RS_CART_COMPONENT rc

where rc.REQUEST_ID=r.REQUEST_ID and rc.PRODUCT_ID=$sku and DATE_FORMAT(DATE(r.DATE_CREATED),'%Y-%m') between '$from_date_final' and '$to_date_final'";
                        $resultDB_2 = $db->execute_query($query_11);
                    } else {
                        $query_11 = "select r.STATUS from RS_CART_REQUEST r,RS_CART_COMPONENT rc

where rc.REQUEST_ID=r.REQUEST_ID and rc.PRODUCT_ID=$sku";

                        $resultDB_2 = $db->execute_query($query_11);
                    }

                    while($row = mysqli_fetch_array($resultDB_2, MYSQL_ASSOC)) {
                        
                        if ($row['STATUS'] == 'Created') {
                            $Created = $Created + 1;

                        } else if ($row['STATUS'] == 'Finalized') {
                            $Finalized = $Finalized + 1;
                        } else if ($row['STATUS'] == 'Received') {
                            $Received = $Received + 1;
                        } else if ($row['STATUS'] === 'On Hold') {
                            $On_hold = $On_hold + 1;
                        }
                        
                        else {
                            $resolved=1;
                        }

                    }
                }

            }

            if ($Created == 0 && $Finalized == 0 && $Received == 0 && $On_hold == 0) {

            } else {
                $result['Created'] = $Created;
                $result['Finalized'] = $Finalized;
                $result['Received'] = $Received;
                $result['On_hold'] = $On_hold;

                $final_result[] = $result;
            }
        }
        
        
      //  die;
        
        return $final_result;
    }
    public static function getRequestByCountryReport($db_config, $from_date, $to_date)
    {
        require_once("databaseutilities.class.php");
        $db = new DatabaseUtilities($db_config);
        $from_date_final = "";
        $to_date_final = "";
        $result = array();
        if (!empty($from_date) && !empty($to_date)) {

            $from_date_1 = explode('-', $from_date);

            $from_date_m = $from_date_1[0];
            $from_date_y = $from_date_1[1];

            $to_date_1 = explode('-', $to_date);

            $to_date_y = $to_date_1[1];
            $to_date_m = $to_date_1[0];

            $from_date_final = $from_date_y . "-" . $from_date_m;
            $to_date_final = $to_date_y . "-" . $to_date_m;

        }

        $query = "SELECT COUNTRY From SHIPMENT_ADDRESS group by COUNTRY";
        $resultDB = $db->execute_query($query);

        $final_result = array();
        while ($row = mysqli_fetch_array($resultDB, MYSQL_ASSOC)) {
            $COUNTRY_1 = $row['COUNTRY'];

            $query_3 = "SELECT COUNTRY_NAME FROM SHIPMENT_ADDRESS sa,COUNTRIES country where sa.COUNTRY=country.ID and sa.COUNTRY=$COUNTRY_1";
            $resultDB_3 = $db->execute_query($query_3);
            if ($row = mysqli_fetch_array($resultDB_3, MYSQL_ASSOC)) {
                $result['COUNTRY_NAME'] = $row['COUNTRY_NAME'];
            }
            $jan = 0;
            $feb = 0;
            $mar = 0;
            $apr = 0;
            $may = 0;
            $june = 0;
            $july = 0;
            $aug = 0;
            $sep = 0;
            $oct = 0;
            $nov = 0;
            $dec = 0;

            $query_1 = "SELECT USER_ID FROM `SHIPMENT_ADDRESS` where COUNTRY=$COUNTRY_1";
            $resultDB_1 = $db->execute_query($query_1);
            while ($row = mysqli_fetch_array($resultDB_1, MYSQL_ASSOC)) {
                $userId = $row['USER_ID'];

                if (!empty($from_date) && !empty($to_date)) {
                    $query_2 = "SELECT MONTH(rcr.DATE_CREATED) as month FROM SHIPMENT_ADDRESS sa,RS_CART_REQUEST rcr where sa.USER_ID=rcr.USER_ID and DATE_FORMAT(rcr.DATE_CREATED,'%Y-%m') >= '$from_date_final' AND DATE_FORMAT(rcr.DATE_CREATED,'%Y-%m') <= '$to_date_final' and rcr.USER_ID=$userId";
                    $resultDB_2 = $db->execute_query($query_2);
                } else {
                    $query_2 = "SELECT MONTH(rcr.DATE_CREATED) as month FROM SHIPMENT_ADDRESS sa,RS_CART_REQUEST rcr where sa.USER_ID=rcr.USER_ID and rcr.USER_ID=$userId and DATE(rcr.DATE_CREATED) >= DATE_SUB(curdate(),INTERVAL 1 YEAR)";
                    $resultDB_2 = $db->execute_query($query_2);
                }
                while ($row = mysqli_fetch_array($resultDB_2, MYSQL_ASSOC)) {
                    $date_created = $row['month'];
                    // echo $date_created;
                    switch ($date_created) {
                        case "1":
                            $jan = $jan + 1;
                            break;
                        case "2":
                            $feb = $feb + 1;
                            break;
                        case "3":
                            $mar = $mar + 1;
                            break;
                        case "4":
                            $apr = $apr + 1;
                            break;
                        case "5":
                            $may = $may + 1;
                            break;
                        case "6":
                            $june = $june + 1;
                            break;
                        case "7":
                            $july = $july + 1;
                            break;
                        case "8":
                            $aug = $aug + 1;
                            break;
                        case "9":
                            $sep = $sep + 1;
                            break;
                        case "10":
                            $oct = $oct + 1;
                            break;
                        case "11":
                            $nov = $nov + 1;
                            break;
                        default:
                            $dec = $dec + 1;
                    }
                }
            }


            if ($jan == 0 && $feb == 0 && $mar == 0 && $apr == 0 && $may == 0 && $june == 0 && $july == 0 && $aug == 0
                && $sep == 0 && $oct == 0 && $nov == 0 && $dec == 0) {
                // $final_result[] = "";
            } else {
                $result['JAN'] = $jan;
                $result['FEB'] = $feb;
                $result['MAR'] = $mar;
                $result['APR'] = $apr;
                $result['MAY'] = $may;
                $result['JUN'] = $june;
                $result['JUL'] = $july;
                $result['AUG'] = $aug;
                $result['SEP'] = $sep;
                $result['OCT'] = $oct;
                $result['NOV'] = $nov;
                $result['DEC'] = $dec;
                $final_result[] = $result;
            }
        }

        return $final_result;
    }
    public static function requestByComponent_old($db_config, $from_date, $to_date)
    {
        require_once("databaseutilities.class.php");
        $db = new DatabaseUtilities($db_config);
        $from_date_final="";
        $to_date_final="";

        $result = array();
        $final_result = array();

        if (!empty($from_date) && !empty($to_date)) {

            $result['FROM_DATE'] = $from_date;
            $result['TO_DATE'] = $to_date;
            $from_date_1 = explode('-', $from_date);
            $from_date_m = $from_date_1[0];
            $from_date_y = $from_date_1[1];
            $to_date_1 = explode('-', $to_date);
            $to_date_y = $to_date_1[1];
            $to_date_m = $to_date_1[0];
            $from_date_final = $from_date_y . "-" . $from_date_m;
            $to_date_final = $to_date_y . "-" . $to_date_m;
        }
        if (!empty($from_date) && !empty($to_date)) {
            $query = "SELECT COMPONENT_ID,PRODUCT_ID,sum(QTY) as t_count,count(REQUEST_ID) as r_count
FROM RS_CART_COMPONENT where DATE_FORMAT(DATE(CREATED_DATE),'%Y-%m') between '$from_date_final' and '$to_date_final'
group by COMPONENT_ID,PRODUCT_ID";
            $resultDB = $db->execute_query($query);
        } else {
            $query = "SELECT COMPONENT_ID,PRODUCT_ID,sum(QTY) as t_count,count(REQUEST_ID) as r_count
FROM RS_CART_COMPONENT 
group by COMPONENT_ID,PRODUCT_ID";
            $resultDB = $db->execute_query($query);
        }

        while ($row = mysqli_fetch_array($resultDB, MYSQL_ASSOC)) {

            if (!empty($from_date) && !empty($to_date)) {
                $query_1 = "SELECT * FROM RS_CART_COMPONENT where COMPONENT_ID='%s' and PRODUCT_ID=%u and DELETED_STATUS='0' and DATE_FORMAT(DATE(CREATED_DATE),'%Y-%m') between '$from_date_final' and '$to_date_final'";
                $resultDB_1 = $db->execute_query($query_1, $row['COMPONENT_ID'], $row['PRODUCT_ID']);
            }
            else{
                $query_1 = "SELECT * FROM RS_CART_COMPONENT where COMPONENT_ID='%s' and PRODUCT_ID=%u and DELETED_STATUS='0'";
                $resultDB_1 = $db->execute_query($query_1, $row['COMPONENT_ID'], $row['PRODUCT_ID']);
            }
            if($resultDB_1->num_rows  >0 ) {
                $query_2 = "SELECT COMPONENT_NAME,COMPONENT_GROUP,SKU FROM PRODUCT_COMPONENT where ID='%s' and SKU=%u";
                $resultDB_2 = $db->execute_query($query_2, $row['COMPONENT_ID'],$row['PRODUCT_ID']);
                if ($row_2 = mysqli_fetch_array($resultDB_2, MYSQL_ASSOC)) {
                    $result['COMPONENT_NAME'] = $row_2['COMPONENT_NAME'];
                    $result['GROUP'] = $row_2['COMPONENT_GROUP'];
                    $result['PRODUCT_ID'] = $row_2['SKU'];
                    $result['isAvailable'] = "";
                }
            }
            else{
                $query_2 = "SELECT COMPONENT_NAME,COMPONENT_GROUP,SKU FROM PRODUCT_COMPONENT where ID='%s' and SKU=%u";
                $resultDB_2 = $db->execute_query($query_2, $row['COMPONENT_ID'], $row['PRODUCT_ID']);
                if($resultDB_2->num_rows >0) {
                    if ($row_2 = mysqli_fetch_array($resultDB_2, MYSQL_ASSOC)) {
                        $result['COMPONENT_NAME'] = $row_2['COMPONENT_NAME'];
                        $result['GROUP'] = $row_2['COMPONENT_GROUP'];
                        $result['PRODUCT_ID'] = $row_2['SKU'];
                        $result['isAvailable'] = "";
                    }
                }
                else{
                    $query_2 = "SELECT COMPONENT_NAME,COMPONENT_GROUP,SKU FROM RS_COMPONENT_LOG where ID='%s' and SKU=%u";
                    $resultDB_2 = $db->execute_query($query_2, $row['COMPONENT_ID'], $row['PRODUCT_ID']);
                    if ($row_2 = mysqli_fetch_array($resultDB_2, MYSQL_ASSOC)) {
                        $result['COMPONENT_NAME'] = $row_2['COMPONENT_NAME'];
                        $result['GROUP'] = $row_2['COMPONENT_GROUP'];
                        $result['PRODUCT_ID'] = $row_2['SKU'];
                        $result['isAvailable'] = "(deleted)";
                    }
                }
            }
            $result['ID'] = $row['COMPONENT_ID'];
            $result['REQUESTED'] = $row['r_count'];
            $result['QUANTITY'] = $row['t_count'];
            $final_result[] = $result;

        }

        function sortByTotalRequest_1($a, $b)
        {
            $a = $a['QUANTITY'];
            $b = $b['QUANTITY'];

            if ($a == $b) return 0;
            return ($a > $b) ? -1 : 1;
        }
        usort($final_result, 'sortByTotalRequest_1');


        $db = NULL;
        return $final_result;
    }
     public static function requestByComponent($db_config, $from_date, $to_date)
    {
        require_once("databaseutilities.class.php");
        $db = new DatabaseUtilities($db_config);
        $from_date_final="";
        $to_date_final="";

        $result = array();
        $final_result = array();

        if (!empty($from_date) && !empty($to_date)) {

            $result['FROM_DATE'] = $from_date;
            $result['TO_DATE'] = $to_date;
            $from_date_1 = explode('-', $from_date);
            $from_date_m = $from_date_1[0];
            $from_date_y = $from_date_1[1];
            $to_date_1 = explode('-', $to_date);
            $to_date_y = $to_date_1[1];
            $to_date_m = $to_date_1[0];
            $from_date_final = $from_date_y . "-" . $from_date_m;
            $to_date_final = $to_date_y . "-" . $to_date_m;
        }
        if (!empty($from_date) && !empty($to_date)) {
            $query = "SELECT COMPONENT_ID,PRODUCT_ID,sum(QTY) as t_count,count(REQUEST_ID) as r_count
FROM RS_CART_COMPONENT where DATE_FORMAT(DATE(CREATED_DATE),'%Y-%m') between '$from_date_final' and '$to_date_final'
group by COMPONENT_ID,PRODUCT_ID";
            $resultDB = $db->execute_query($query);
        } else {
            $query = "SELECT COMPONENT_ID,PRODUCT_ID,sum(QTY) as t_count,count(REQUEST_ID) as r_count
FROM RS_CART_COMPONENT 
group by COMPONENT_ID,PRODUCT_ID";
            $resultDB = $db->execute_query($query);
        }

        while ($row = mysqli_fetch_array($resultDB, MYSQL_ASSOC)) {

            if (!empty($from_date) && !empty($to_date)) {
                $query_1 = "SELECT * FROM RS_CART_COMPONENT where COMPONENT_ID='%s' and PRODUCT_ID=%u and DATE_FORMAT(DATE(CREATED_DATE),'%Y-%m') between '$from_date_final' and '$to_date_final'";
                $resultDB_1 = $db->execute_query($query_1, $row['COMPONENT_ID'], $row['PRODUCT_ID']);
            }
            else{
                $query_1 = "SELECT * FROM RS_CART_COMPONENT where COMPONENT_ID='%s' and PRODUCT_ID=%u";
                $resultDB_1 = $db->execute_query($query_1, $row['COMPONENT_ID'], $row['PRODUCT_ID']);
            }
          
                $query_2 = "SELECT COMPONENT_NAME,COMPONENT_GROUP,SKU FROM PRODUCT_COMPONENT where ID='%s' and SKU=%u";
                $resultDB_2 = $db->execute_query($query_2, $row['COMPONENT_ID'], $row['PRODUCT_ID']);
                if($resultDB_2->num_rows >0) {
                    if ($row_2 = mysqli_fetch_array($resultDB_2, MYSQL_ASSOC)) {
                        $result['COMPONENT_NAME'] = $row_2['COMPONENT_NAME'];
                        $result['GROUP'] = $row_2['COMPONENT_GROUP'];
                        $result['PRODUCT_ID'] = $row_2['SKU'];
                        $result['isAvailable'] = "";
                    }
                }
                else{
                    $query_2 = "SELECT COMPONENT_NAME,COMPONENT_GROUP,SKU FROM RS_COMPONENT_LOG where ID='%s' and SKU=%u";
                    $resultDB_2 = $db->execute_query($query_2, $row['COMPONENT_ID'], $row['PRODUCT_ID']);
                    if ($row_2 = mysqli_fetch_array($resultDB_2, MYSQL_ASSOC)) {
                        $result['COMPONENT_NAME'] = $row_2['COMPONENT_NAME'];
                        $result['GROUP'] = $row_2['COMPONENT_GROUP'];
                        $result['PRODUCT_ID'] = $row_2['SKU'];
                        $result['isAvailable'] = "(deleted)";
                    }
                }
          
            $result['ID'] = $row['COMPONENT_ID'];
            $result['REQUESTED'] = $row['r_count'];
            $result['QUANTITY'] = $row['t_count'];
            $final_result[] = $result;

        }

        function sortByTotalRequest_2($a, $b)
        {
            $a = $a['QUANTITY'];
            $b = $b['QUANTITY'];

            if ($a == $b) return 0;
            return ($a > $b) ? -1 : 1;
        }
        usort($final_result, 'sortByTotalRequest_2');


        $db = NULL;
        return $final_result;
    }
    public static function weeklyEmailBlastReport_thisWeek_old($db_config)
    {
        require_once("databaseutilities.class.php");
        $db = new DatabaseUtilities($db_config);

        $today = date("Y-m-d");
        $oneweekago = date('Y-m-d', strtotime('-7 days'));

        $query = "SELECT COMPONENT_ID,PRODUCT_ID,sum(QTY) as t_count
FROM RS_CART_COMPONENT where DATE(CREATED_DATE) between '%s' and '%s'
group by COMPONENT_ID,PRODUCT_ID";
        $resultDB = $db->execute_query($query, $oneweekago, $today);
        $result = array();
        $final_result = array();
        while ($row = mysqli_fetch_array($resultDB, MYSQL_ASSOC)) {
            $query_1 = "SELECT * FROM RS_CART_COMPONENT where COMPONENT_ID='%s' and PRODUCT_ID=%u and DELETED_STATUS='0' and DATE(CREATED_DATE) between '%s' and '%s'";
            $resultDB_1 = $db->execute_query($query_1, $row['COMPONENT_ID'],$row['PRODUCT_ID'],$oneweekago,$today);
            if($resultDB_1->num_rows  >0 ) {
                $query_2 = "SELECT COMPONENT_NAME,COMPONENT_GROUP,SKU FROM PRODUCT_COMPONENT where ID='%s' and SKU=%u";
                $resultDB_2 = $db->execute_query($query_2, $row['COMPONENT_ID'],$row['PRODUCT_ID']);
                if ($row_2 = mysqli_fetch_array($resultDB_2, MYSQL_ASSOC)) {
                    $result['COMPONENT_NAME'] = $row_2['COMPONENT_NAME'];
                    $result['GROUP'] = $row_2['COMPONENT_GROUP'];
                    $result['PRODUCT_ID'] = $row_2['SKU'];
                    $result['isAvailable'] = "";
                }
            }
            else{
                $query_2 = "SELECT COMPONENT_NAME,COMPONENT_GROUP,SKU FROM PRODUCT_COMPONENT where ID='%s' and SKU=%u";
                $resultDB_2 = $db->execute_query($query_2, $row['COMPONENT_ID'], $row['PRODUCT_ID']);
                if($resultDB_2->num_rows >0) {
                    if ($row_2 = mysqli_fetch_array($resultDB_2, MYSQL_ASSOC)) {
                        $result['COMPONENT_NAME'] = $row_2['COMPONENT_NAME'];
                        $result['GROUP'] = $row_2['COMPONENT_GROUP'];
                        $result['PRODUCT_ID'] = $row_2['SKU'];
                        $result['isAvailable'] = "";
                    }
                }
                else{
                    $query_2 = "SELECT COMPONENT_NAME,COMPONENT_GROUP,SKU FROM RS_COMPONENT_LOG where ID='%s' and SKU=%u";
                    $resultDB_2 = $db->execute_query($query_2, $row['COMPONENT_ID'], $row['PRODUCT_ID']);
                    if ($row_2 = mysqli_fetch_array($resultDB_2, MYSQL_ASSOC)) {
                        $result['COMPONENT_NAME'] = $row_2['COMPONENT_NAME'];
                        $result['GROUP'] = $row_2['COMPONENT_GROUP'];
                        $result['PRODUCT_ID'] = $row_2['SKU'];
                        $result['isAvailable'] = "(deleted)";
                    }
                }
            }
            $result['ID'] = $row['COMPONENT_ID'];
            $result['QUANTITY'] = $row['t_count'];
            $final_result[] = $result;
        }


        function sortByTotalRequest_4($a, $b)
        {
            $a = $a['QUANTITY'];
            $b = $b['QUANTITY'];

            if ($a == $b) return 0;
            return ($a > $b) ? -1 : 1;
        }
        usort($final_result, 'sortByTotalRequest_4');

        $db = NULL;
        return $final_result;

    }
     public static function weeklyEmailBlastReport_thisWeek($db_config)
    {
        require_once("databaseutilities.class.php");
        $db = new DatabaseUtilities($db_config);

        $today = date("Y-m-d");
        $oneweekago = date('Y-m-d', strtotime('-7 days'));

        $query = "SELECT COMPONENT_ID,PRODUCT_ID,sum(QTY) as t_count
FROM RS_CART_COMPONENT where DATE(CREATED_DATE) between '%s' and '%s'
group by COMPONENT_ID,PRODUCT_ID";
        $resultDB = $db->execute_query($query, $oneweekago, $today);
        $result = array();
        $final_result = array();
        while ($row = mysqli_fetch_array($resultDB, MYSQL_ASSOC)) {
                $query_2 = "SELECT COMPONENT_NAME,COMPONENT_GROUP,SKU FROM PRODUCT_COMPONENT where ID='%s' and SKU=%u";
                $resultDB_2 = $db->execute_query($query_2, $row['COMPONENT_ID'], $row['PRODUCT_ID']);
                if($resultDB_2->num_rows >0) {
                    if ($row_2 = mysqli_fetch_array($resultDB_2, MYSQL_ASSOC)) {
                        $result['COMPONENT_NAME'] = $row_2['COMPONENT_NAME'];
                        $result['GROUP'] = $row_2['COMPONENT_GROUP'];
                        $result['PRODUCT_ID'] = $row_2['SKU'];
                        $result['isAvailable'] = "";
                    }
                }
                else{
                    $query_2 = "SELECT COMPONENT_NAME,COMPONENT_GROUP,SKU FROM RS_COMPONENT_LOG where ID='%s' and SKU=%u";
                    $resultDB_2 = $db->execute_query($query_2, $row['COMPONENT_ID'], $row['PRODUCT_ID']);
                    if ($row_2 = mysqli_fetch_array($resultDB_2, MYSQL_ASSOC)) {
                        $result['COMPONENT_NAME'] = $row_2['COMPONENT_NAME'];
                        $result['GROUP'] = $row_2['COMPONENT_GROUP'];
                        $result['PRODUCT_ID'] = $row_2['SKU'];
                        $result['isAvailable'] = "(deleted)";
                    }
                }
          
            $result['ID'] = $row['COMPONENT_ID'];
            $result['QUANTITY'] = $row['t_count'];
            $final_result[] = $result;
        }
        function sortByTotalRequest_5($a, $b)
        {
            $a = $a['QUANTITY'];
            $b = $b['QUANTITY'];

            if ($a == $b) return 0;
            return ($a > $b) ? -1 : 1;
        }
        usort($final_result, 'sortByTotalRequest_5');
        
        $final_result_1=array_slice($final_result,0,10);

        $db = NULL;
        return $final_result_1;

    }   
    public static function weeklyEmailBlastReport_pastSixMonth_old($db_config)
    {
        require_once("databaseutilities.class.php");
        $db = new DatabaseUtilities($db_config);

        $query = "SELECT COMPONENT_ID,PRODUCT_ID,sum(QTY) as t_count
FROM RS_CART_COMPONENT where DATE(CREATED_DATE) >= DATE_SUB(now(), INTERVAL 6 MONTH)
group by COMPONENT_ID,PRODUCT_ID";

        $resultDB = $db->execute_query($query);
        $result = array();

        while ($row = mysqli_fetch_array($resultDB, MYSQL_ASSOC)) {
            $query_1 = "SELECT * FROM RS_CART_COMPONENT where COMPONENT_ID='%s' and PRODUCT_ID=%u and DELETED_STATUS='0' and DATE(CREATED_DATE) >= DATE_SUB(now(), INTERVAL 6 MONTH)";
            $resultDB_1 = $db->execute_query($query_1, $row['COMPONENT_ID'],$row['PRODUCT_ID']);
            if($resultDB_1->num_rows  >0 ) {
                $query_2 = "SELECT COMPONENT_NAME,COMPONENT_GROUP,SKU FROM PRODUCT_COMPONENT where ID='%s' and SKU=%u";
                $resultDB_2 = $db->execute_query($query_2, $row['COMPONENT_ID'],$row['PRODUCT_ID']);
                if ($row_2 = mysqli_fetch_array($resultDB_2, MYSQL_ASSOC)) {
                    $result['COMPONENT_NAME'] = $row_2['COMPONENT_NAME'];
                    $result['GROUP'] = $row_2['COMPONENT_GROUP'];
                    $result['PRODUCT_ID'] = $row_2['SKU'];
                    $result['isAvailable'] = "";
                }
            }
            else{
                $query_2 = "SELECT COMPONENT_NAME,COMPONENT_GROUP,SKU FROM PRODUCT_COMPONENT where ID='%s' and SKU=%u";
                $resultDB_2 = $db->execute_query($query_2, $row['COMPONENT_ID'], $row['PRODUCT_ID']);
                if($resultDB_2->num_rows >0) {
                    if ($row_2 = mysqli_fetch_array($resultDB_2, MYSQL_ASSOC)) {
                        $result['COMPONENT_NAME'] = $row_2['COMPONENT_NAME'];
                        $result['GROUP'] = $row_2['COMPONENT_GROUP'];
                        $result['PRODUCT_ID'] = $row_2['SKU'];
                        $result['isAvailable'] = "";
                    }
                }
                else{
                    $query_2 = "SELECT COMPONENT_NAME,COMPONENT_GROUP,SKU FROM RS_COMPONENT_LOG where ID='%s' and SKU=%u";
                    $resultDB_2 = $db->execute_query($query_2, $row['COMPONENT_ID'], $row['PRODUCT_ID']);
                    if ($row_2 = mysqli_fetch_array($resultDB_2, MYSQL_ASSOC)) {
                        $result['COMPONENT_NAME'] = $row_2['COMPONENT_NAME'];
                        $result['GROUP'] = $row_2['COMPONENT_GROUP'];
                        $result['PRODUCT_ID'] = $row_2['SKU'];
                        $result['isAvailable'] = "(deleted)";
                    }
                }
            }
            $result['ID'] = $row['COMPONENT_ID'];
            $result['QUANTITY'] = $row['t_count'];
            $final_result[] = $result;
        }

        function sortByTotalRequest_6($a, $b)
        {
            $a = $a['QUANTITY'];
            $b = $b['QUANTITY'];

            if ($a == $b) return 0;
            return ($a > $b) ? -1 : 1;
        }
        usort($final_result, 'sortByTotalRequest_6');

        $db = NULL;
        return $final_result;

    }
    
    public static function weeklyEmailBlastReport_pastSixMonth($db_config)
    {
        require_once("databaseutilities.class.php");
        $db = new DatabaseUtilities($db_config);

        $query = "SELECT COMPONENT_ID,PRODUCT_ID,sum(QTY) as t_count
FROM RS_CART_COMPONENT where DATE(CREATED_DATE) >= DATE_SUB(now(), INTERVAL 6 MONTH)
group by COMPONENT_ID,PRODUCT_ID";

        $resultDB = $db->execute_query($query);
        $result = array();
        while ($row = mysqli_fetch_array($resultDB, MYSQL_ASSOC)) {
          
                $query_2 = "SELECT COMPONENT_NAME,COMPONENT_GROUP,SKU FROM PRODUCT_COMPONENT where ID='%s' and SKU=%u";
                $resultDB_2 = $db->execute_query($query_2, $row['COMPONENT_ID'], $row['PRODUCT_ID']);
                if($resultDB_2->num_rows >0) {
                    if ($row_2 = mysqli_fetch_array($resultDB_2, MYSQL_ASSOC)) {
                        $result['COMPONENT_NAME'] = $row_2['COMPONENT_NAME'];
                        $result['GROUP'] = $row_2['COMPONENT_GROUP'];
                        $result['PRODUCT_ID'] = $row_2['SKU'];
                        $result['isAvailable'] = "";
                    }
                }
                else{
                    $query_2 = "SELECT COMPONENT_NAME,COMPONENT_GROUP,SKU FROM RS_COMPONENT_LOG where ID='%s' and SKU=%u";
                    $resultDB_2 = $db->execute_query($query_2, $row['COMPONENT_ID'], $row['PRODUCT_ID']);
                    if ($row_2 = mysqli_fetch_array($resultDB_2, MYSQL_ASSOC)) {
                        $result['COMPONENT_NAME'] = $row_2['COMPONENT_NAME'];
                        $result['GROUP'] = $row_2['COMPONENT_GROUP'];
                        $result['PRODUCT_ID'] = $row_2['SKU'];
                        $result['isAvailable'] = "(deleted)";
                    }
                }
          
            $result['ID'] = $row['COMPONENT_ID'];
            $result['QUANTITY'] = $row['t_count'];
            $final_result[] = $result;
        }
  
        function sortByTotalRequest_7($a, $b)
        {
            $a = $a['QUANTITY'];
            $b = $b['QUANTITY'];

            if ($a == $b) return 0;
            return ($a > $b) ? -1 : 1;
        }
        usort($final_result, 'sortByTotalRequest_7');
        $final_result_1=array_slice($final_result,0,10);
        $db = NULL;
        return $final_result_1;

    }
    
    public static function weeklyEmailBlastReport_overEightWeeksOld($db_config)
    {
        require_once("databaseutilities.class.php");
        $db = new DatabaseUtilities($db_config);
        $query = "SELECT * From RS_CART_REQUEST where DATE(DATE_SENT) <= DATE_SUB(curdate(),INTERVAL 8 WEEK) and STATUS IN ('Finalized','Received') order by DATE_SENT LIMIT 2";
        $resultDB = $db->execute_query($query);
        $result = array();
        while ($row = mysqli_fetch_array($resultDB, MYSQL_ASSOC)) {
            $result[] = $row;
        }  
        return $result;
    }
    
    public static function weeklyEmailBlastReport_frequentFlyresThisWeek($db_config)
    {
        require_once("databaseutilities.class.php");
        $db = new DatabaseUtilities($db_config);
        $query = "SELECT USER_ID From RS_CART_REQUEST where DATE(DATE_CREATED) >= DATE_SUB(curdate(),INTERVAL 1 WEEK) group by USER_ID";
        $resultDB = $db->execute_query($query);
        $result = array();
        $final_result = array();
        while ($row = mysqli_fetch_array($resultDB, MYSQL_ASSOC)) {
            $USER_ID = $row['USER_ID'];
            $result['USER_ID'] = $USER_ID;
           
            $query_1 = "SELECT SUM(QTY) as c_count,COUNT(DISTINCT REQUEST_ID) as count_1 FROM `RS_CART_COMPONENT` where USER_ID=$USER_ID";
            $resultDB_1 = $db->execute_query($query_1);
          
            if ($row_1 = mysqli_fetch_array($resultDB_1, MYSQL_ASSOC)) {
                $count_cmp_1 = $row_1['c_count'];
                $total_request = $row_1['count_1'];
            }
            
            $result['TOTAL_REQUEST'] =$total_request;
            $result['TOTAL_COMPONENT'] = $count_cmp_1;

            $query_3 = "SELECT NICK_NAME FROM `USERS` where ID=$USER_ID";
            $resultDB_3 = $db->execute_query($query_3);

            if ($row = mysqli_fetch_array($resultDB_3, MYSQL_ASSOC)) {
                $result['USERNAME'] = $row['NICK_NAME'];
            }
            $final_result[] = $result;
        }
        
        function sortByTotalRequest_8($a, $b)
        {
            $a = $a['TOTAL_REQUEST'];
            $b = $b['TOTAL_REQUEST'];

            if ($a == $b) return 0;
            return ($a > $b) ? -1 : 1;
        }
        
        
        usort($final_result, 'sortByTotalRequest_8');
       
        $final_result_1=array_slice($final_result,0,5);
        
        return $final_result_1;
    }
    public static function weeklyEmailBlastReport_requestStatus($db_config)
    {
        require_once("databaseutilities.class.php");
        $db = new DatabaseUtilities($db_config);
        $result = array();
        $final_result=array();
        $query_1 = "SELECT COUNT(*) as count_1
FROM RS_CART_REQUEST where STATUS IN ('Finalised','Received')";
        $resultDB_1 = $db->execute_query($query_1);

        if ($row_1 = mysqli_fetch_array($resultDB_1, MYSQL_ASSOC)) {
            $result['total_request'] = $row_1['count_1'];
        }

        $query_2 = "SELECT COUNT(*) as count_2
FROM RS_CART_REQUEST where DATE(DATE_CREATED) >= DATE_SUB(curdate(),INTERVAL 1 WEEK) ";
        $resultDB_2 = $db->execute_query($query_2);

        if ($row_2 = mysqli_fetch_array($resultDB_2, MYSQL_ASSOC)) {
            $result['total_created'] = $row_2['count_2'];
        }

        $query_3 = "SELECT COUNT(*) as count_3
FROM RS_CART_REQUEST where STATUS='Resolved' and DATE(DATE_CREATED) >= DATE_SUB(curdate(),INTERVAL 1 WEEK) ";
        $resultDB_3 = $db->execute_query($query_3);

        if ($row_3 = mysqli_fetch_array($resultDB_3, MYSQL_ASSOC)) {
            $result['total_resolved'] = $row_3['count_3'];
        }
        $final_result[]=$result;
        $db = NULL;
        return $final_result;
    }
    function WeeklySendEmailToGroup($title, $message) {
        global $wes_config;
        global $db_config;
        $cur_date=date("m/d/Y");
        $db = new DatabaseUtilities($db_config);
        $query = "select * from RS_WEEKLY_EMAIL";
        $result = $db->execute_query($query);
        $sentIds = array();
        while ($row = mysqli_fetch_array($result, MYSQL_ASSOC)) {
            $email = $row["EMAIL"];
            Self::WeeklyInstantSendDistributor($email, $message, "", $title, $wes_config, "WizKids Replacement System Report - $cur_date", "orders@wizkids.com", array("distributor"), array("user" => $row["ID"], "email" => 'nikul.chauhan@bacancytechnology.com'));
            $sentIds[] = $row["ID"];
        }
    }
    public static function WeeklyInstantSendDistributor($mailData, $contentHtml, $contentText, $subject, $config, $fromName = "No Reply", $fromEmail = NULL, $tags = NULL, $metadata = NULL) {
        require_once( "Mailchimp/Mandrill.php" );
        $mandrill = new Mandrill($config["mailchimp"]->api_key);
        if (is_null($mailData) || $mailData == "") {
            return false;
        }

        if (is_null($fromEmail)) {
            $fromEmail = $config["mailchimp"]->noreply;
        }

        if (!is_null($tags) && !is_array($tags)) {
            $tags = array($tags);
        }

        if (!is_null($metadata) && !is_array($metadata)) {
            $metadata = array($metadata);
        }


            $message = array(
                'html' => $contentHtml,
                'text' => $contentText,
                'subject' => $subject,
                'from_email' => $fromEmail,
                'from_name' => $fromName,
                'to' => array(
                    array(
                        'email' => $mailData,
                        'type' => 'to'
                    )
                ),
                'tags' => $tags,
                'metadata' => $metadata,
                'track_opens' => true,
                'track_clicks' => true,

            );

        $async = false; //enables background mode for bulk email
        $ip_pool = ''; //if you have a dedicated ip pool with mandrill
        $send_at = ''; //schedule a time to send email, only for prepaid accounts

        $bSuccess = true;
        try {

            $result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);

            if (isset($result["reject_reason"]) && $result["reject_reason"] != "") {
                error_log("Mandrill email rejection addr:{$mailData->email}, reason: {$result["reject_reason"]}");
                $bSuccess = false;
            }
        } catch (Mandrill_Error $e) {
//            echo "<pre>";
//            print_r($e);
//            die;
            error_log('A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage());
            self::SendMail($mailData->email, $subject, $contentHtml, $config["mailchimp"]->noreply);
            $bSuccess = false;
        }

        return $bSuccess;
    }
    public static function SendMail($recipients, $subject, $msg, $from) {
        // require_once('Mailchimp.php');
        $headers = "From: {$from}\r\n";
        $headers .= "Content-type: text/html\r\n";
        $result=array();
        if (is_array($recipients)) {
            foreach ($recipients as $to) {
                if (mail($to, $subject, $msg, $headers)) {
                    $result[] = "Message successfully sent to {$to}";
                }
            }
        } else {

            if (mail($recipients, $subject, $msg, $headers)) {

                $result[] = "Message successfully sent to {$recipients}";
            }
        }

        return count($result) > 0 ? $result : false;
    }
}

?>