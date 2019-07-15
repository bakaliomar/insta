<?php namespace ma\applications\mailtng\helpers
{
    if (!defined('MAILTNG_FMW')) die('<pre>It\'s forbidden to access these files directly , access should be only via index.php </pre>');
    /**
     * @framework       MailTng Framework
     * @version         1.1
     * @author          MailTng Team
     * @copyright       Copyright (c) 2015 - 2016.	
     * @license		
     * @link	
     */
    use ma\mailtng\core\Base as Base;
    /**
     * @name            StatsHelper.class 
     * @description     The StatsHelper class
     * @package		ma\applications\mailtng\helpers
     * @category        Helper
     * @author		MailTng Team			
     */
    class StatsHelper extends Base
    {
        /**
         * @name buildStatsTableForExcel
         * @description build stats table for excel
         * @access static
         * @param array $statsResult
         * @return array
         */
        public static function buildStatsTableForExcel($statsResult)
        {
            $result = array();
            $excel = "Server,IP,ISP,Date,Total,Delivered,Bounced" . PHP_EOL;

            if(isset($statsResult) && count($statsResult))
            {
                # gathering the data 
                foreach ($statsResult as $row) 
                { 
                    $line = array();
                    $line['server'] = !empty($row['server']) ? $row['server'] : '-';
                    $line['ip'] = !empty($row['ip']) ? $row['ip'] : '-';
                    $line['isp'] = !empty($row['isp']) ? $row['isp'] : '-';
                    $date = new \DateTime($row['date']);
                    $line['date'] = !empty($date->format('d-m-Y')) ? $date->format('d-m-Y') : '-';
                    
                    if(isset($result[trim($row['server'])][trim($row['ip'])][trim($line['date'])]) && count($result[trim($row['server'])][trim($row['ip'])][trim($line['date'])]))
                    {
                        $line['total'] = intval($row['total']) + intval($result[trim($row['server'])][trim($row['ip'])][trim($line['date'])]['total']);
                        $line['delivered'] = intval($row['delivered']) + intval($result[trim($row['server'])][trim($row['ip'])][trim($line['date'])]['delivered']);
                        $line['bounced'] = intval($row['bounced']) + intval($result[trim($row['server'])][trim($row['ip'])][trim($line['date'])]['bounced']);
                    }
                    else
                    {
                        $line['total'] = $row['total'];
                        $line['delivered'] = $row['delivered'];
                        $line['bounced'] = $row['bounced'];
                    }
                    
                    # prevent empty strings
                    $line['total'] = !empty($line['total']) ? $line['total'] : '0';
                    $line['delivered'] = !empty($line['delivered']) ? $line['delivered'] : '0';
                    $line['bounced'] = !empty($line['bounced']) ? $line['bounced'] : '0';

                    $result[trim($row['server'])][trim($row['ip'])][trim($line['date'])] = $line;
                }
                
                # format it 
                
                foreach ($result as $server)
                {
                    foreach ($server as $ips)
                    {
                        foreach ($ips as $date => $line) 
                        {
                            $excelArray = array(
                                $line['server'],
                                $line['ip'],
                                $line['isp'],
                                $line['date'],
                                $line['total'],
                                $line['delivered'],
                                $line['bounced']
                            );
                            
                            $excel .= implode(",",$excelArray) . PHP_EOL;
                        }
                    }
                }
            }

            return trim($excel,PHP_EOL);
        }
        
        /**
         * @name buildStatsTable
         * @description build stats table
         * @access static
         * @param array $statsResult
         * @return array
         */
        public static function buildStatsTable($statsResult,$startDate,$endDate)
        {
            $result = array();

            if(isset($statsResult) && count($statsResult))
            {
                # gathering the data 
                foreach ($statsResult as $row) 
                { 
                    $line = array();
                    $line['server'] = $row['server'];
                    $line['ip'] = $row['ip'];
                    $line['isp'] = $row['isp'];
                    $date = new \DateTime($row['date']);
                    $line['date'] = $date->format('d-m-Y');
                    
                    if(isset($result[trim($row['server'])][trim($row['ip'])][trim($line['date'])]) && count($result[trim($row['server'])][trim($row['ip'])][trim($line['date'])]))
                    {
                        $line['total'] = intval($row['total']) + intval($result[trim($row['server'])][trim($row['ip'])][trim($line['date'])]['total']);
                        $line['delivered'] = intval($row['delivered']) + intval($result[trim($row['server'])][trim($row['ip'])][trim($line['date'])]['delivered']);
                        $line['bounced'] = intval($row['bounced']) + intval($result[trim($row['server'])][trim($row['ip'])][trim($line['date'])]['bounced']);
                    }
                    else
                    {
                        $line['total'] = $row['total'];
                        $line['delivered'] = $row['delivered'];
                        $line['bounced'] = $row['bounced'];
                    }

                    # total
                    $result[trim($row['server'])][trim($row['ip'])]['total']['server'] = $line['server'];
                    $result[trim($row['server'])][trim($row['ip'])]['total']['ip'] = $line['ip'];
                    $result[trim($row['server'])][trim($row['ip'])]['total']['isp'] = $line['isp'];
                    
                    $stringDate = '';
                    $date = new \DateTime($startDate);
                    $stringDate .= "<b>" . $date->format('d-m-Y') . "</b> to <b>";
                    $date = new \DateTime($endDate);
                    $stringDate .= $date->format('d-m-Y') . "</b>";
                    
                    $result[trim($row['server'])][trim($row['ip'])]['total']['date'] = $stringDate;
                    $result[trim($row['server'])][trim($row['ip'])]['total']['total'] = intval($row['total']) + intval($result[trim($row['server'])][trim($row['ip'])]['total']['total']);
                    $result[trim($row['server'])][trim($row['ip'])]['total']['delivered'] = intval($row['delivered']) + intval($result[trim($row['server'])][trim($row['ip'])]['total']['delivered']);
                    $result[trim($row['server'])][trim($row['ip'])]['total']['bounced'] = intval($row['bounced']) + intval($result[trim($row['server'])][trim($row['ip'])]['total']['bounced']);

                    $result[trim($row['server'])][trim($row['ip'])][trim($line['date'])] = $line;
                }
            }
            
            return $result;
        }
        
        /**
         * @name buildStatsTable
         * @description build stats table
         * @access static
         * @param array $statsResult
         * @return array
         */
        public static function buildStatsHTMLTable($statsResult,$startDate,$endDate)
        {
            $html = '';
            $result = self::buildStatsTable($statsResult,$startDate,$endDate);
            $dateDifferenceObject = date_diff(date_create($endDate),date_create($startDate));
            $diff = (isset($dateDifferenceObject) && is_object($dateDifferenceObject)) ?  intval($dateDifferenceObject->days) : 0;

            if(isset($result) && count($result))
            {
                foreach ($result as $server)
                {
                    foreach ($server as $ips)
                    {
                        $html .= "<tr>";
                        $details = "<table class='table table-bordered table-striped table-condensed'>";
                        $detailsHead = "<thead><tr>";
                        $detailsBody = "<tbody>";
                        
                        foreach ($ips['total'] as $key => $column) 
                        {
                            if(empty($column) && $column !== 0 && $column !== "0")
                            {
                                $html .= "<td style='text-align:center'>";
                                $html .= '-';
                                $html .= "</td>";
                            }
                            else
                            {
                                $html .= (in_array($key,array('total','delivered','bounced'))) ? "<td style='text-align:center'>" : "<td>";
                                $html .= $column;
                                $html .= "</td>";
                            }
                      
                            if(!in_array($key,array('server','ip','isp')))
                            {
                                $detailsHead .= "<th style='text-align:center'>";
                                $detailsHead .= $key;
                                $detailsHead .= "</th>";
                            }
                        }

                        $detailsHead .= "</tr></thead>";
                        
                        foreach ($ips as $key => $ip) 
                        {
                            if($key != 'total')
                            {
                                $detailsBody .= "<tr>";
                        
                                foreach ($ip as $key => $column) 
                                {
                                    if(!in_array($key,array('server','ip','isp')))
                                    {
                                        if(empty($column)  && $column !== 0 && $column !== "0")
                                        {
                                            $detailsBody .= "<td style='text-align:center'>";
                                            $detailsBody .= '-';
                                            $detailsBody .= "</td>";
                                        }
                                        else
                                        {
                                            $detailsBody .= "<td style='text-align:center'>";
                                            $detailsBody .= $column;
                                            $detailsBody .= "</td>";
                                        }
                                    }
                                }
                                
                                
                                $detailsBody .= "</tr>";
                            }
                        }
                        
                        $detailsBody .= "</tbody>";
                        
                        $details .= $detailsHead . $detailsBody . "</table>";
                        
                        $html .= "<td style='text-align:center;width: 3px;'>";
                        
                        if($diff == 0)
                        {
                            $html .= "<a href='javascript:;' style='cursor:default;color:#ccc;'><i class='fa fa-list'></i></a> ";
                        }
                        else
                        {
                            $html .= "<a class='stats-details' href='#sub-rows-modal' role='button' data-toggle='modal' data-server='".$ips['total']['server']."' data-ip='".$ips['total']['ip']."' data-details='".base64_encode($details)."'><i class='fa fa-list'></i></a> ";
                        }

                        $html .= "</td>";
                        
                        $html .= "</tr>";
                    }
                }
            }

            return $html;
        }
    }
}