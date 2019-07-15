<?php
/**
 * @name            main.php 
 * @description     helper script that contains some usefull methods 
 * @package         .
 * @category        Native Script
 * @author          MailTng Team			
 */


/**
 * @name createIpProgressFile
 * @description create drop ip progress file
 * @access static
 * @param integer $dropId
 * @param string $dropStatus
 * @param integer $dropTotal
 * @return boolean
 */
function createIpProgressFile($dropId,$dropStatus,$dropTotal,$ipTotal,$ipId)
{
    $directory =  '/usr/mailtng/tmp/proccess/drop_' . $dropId;
    $fileName = $directory . '/prgs_' . $ipId . '.csv';
    $content = "drop_id,status,total,total_ip,progress" . PHP_EOL;
    $content .= "{$dropId},{$dropStatus},{$dropTotal},{$ipTotal},0";
    
    # create the folder 
    if(!file_exists($directory))
    {
        mkdir($directory);
    }

    # make it for apache
    exec("chown -R apache:apache /usr/mailtng/tmp/proccess/");
    
    # create the file
    return file_put_contents($fileName, $content);     
}

/**
 * @name getIpProgressFile
 * @description gets the drop ip progress file
 * @access static
 * @param integer $dropId
 * @return array
 */
function getIpProgressFile($dropId,$ipId)
{
    $fileName = '/usr/mailtng/tmp/proccess/drop_' . $dropId . '/prgs_' . $ipId . '.csv';
    $csv = array_map('str_getcsv', file($fileName)); 
    array_walk($csv, function(&$a) use ($csv) { $a = array_combine($csv[0], $a); });
    array_shift($csv);
    return $csv;
}

/**
 * @name updateIpProgressFile
 * @description update drop ip progress file
 * @access static
 * @param array $data
 * @param integer $ipId
 * @return boolean
 */
function updateIpProgressFile($data,$ipId)
{
    $fileName = '/usr/mailtng/tmp/proccess/drop_' . $data['drop_id'] . '/prgs_' . $ipId . '.csv';
    $content = "drop_id,status,total,total_ip,progress" . PHP_EOL;
    $content .= "{$data['drop_id']},{$data['status']},{$data['total']},{$data['total_ip']},{$data['progress']}";
    return file_put_contents($fileName, $content);     
}

/**
 * @name completeDrop
 * @description check if the drop is completed 
 * @access static
 * @param array $data
 * @return boolean
 */
function completeDrop($data,$interrupted = false,$filePath = null)
{
    $returnValue = false;
    $url = "";        
    $ips = array_keys($data['ips-pickup-files']);
    
    if(count($ips))
    {
        $progress = 0;
        $total = 0;
        $status = '';
        
        foreach ($ips as $ipId) 
        {
            # get the progress file 
            $result = getIpProgressFile($data['drop-id'],intval($ipId));
            
            if(count($result))
            {
                $status = trim($result[0]['status']);
                if($status == 'in-progress')
                {
                    $progress += intval($result[0]['progress']);
                    $total = intval($result[0]['total']);
                }
            }
        }
        
        if($progress == $total && $interrupted == false)
        {
            $status = 'completed';
            $finishTime = date('Y-m-d H:i:s');

            # send the request to mailtng API to complete the drop 
            sendPostRequest($url, array(
                'api_key' => 'x0ja8s4a3duqk9e2w6vga91hrvi7t14wrdxpv754aql055tr2ee2d59b6hop',
                'func_name' => 'complete_drop',
                'drop_id' => $data['drop-id'],
                'status' => $status,
                'progress' => $progress,
                'finish_time' => $finishTime
            ));
            
            # remove drop file
            if($filePath != null && file_exists($filePath))
            {
                unlink($filePath);
            }
        }
    }
    
    return $returnValue;
}

/**
 * @name generateRandomString
 * @description generates random string
 * @access static
 * @param integer $length
 * @return boolean
 */
function generateRandomString($length = 10) 
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) 
    {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    
    return $randomString;
}

/**
 * @name msleep
 * @description delays execution of the script by the given time.
 * @access static
 * @param mixed $time Time to pause script execution. Can be expressed
 * @return boolean
 */
function msleep($time)
{
    usleep($time * 1000000);
}

/**
 * @name sendPostRequest
 * @description send post request
 * @access public
 * @param string $url
 * @param boolean $data
 * @return mixed
 */
function sendPostRequest($url,$data) 
{
    $response = null;

    # preparing the post data
    $post = array();

    $post = array_merge($post,$data);
    $postFields = http_build_query($post);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$postFields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

/**
 * @name cmd
 * @description executes a system command
 * @access static
 * @param string $command
 * @param true $outputInArray
 * @return array
 */
function cmd($command, $outputInArray = false) 
{
    $result = array("output" => "", "error" => "");
    
    if (isset($command) && $command != '') 
    {
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w"),
        );
        $pipes = array();
        $process = proc_open($command, $descriptorspec, $pipes, dirname(__FILE__), null);

        if (is_resource($process)) 
        {
            $result["output"] = $outputInArray ? explode("\n", trim(stream_get_contents($pipes[1]))) : stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $result["error"] = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            // close the proccess
            proc_close($process);
        }
    }
    return $result;
}

/**
 * @name decrypt
 * @description decrypt an AES 256 Algorithem encrypted value
 * @param string $value
 * @param string
 * @return
 */
function decrypt($value)
{
    $secretKey = "vY?{Uq~Xxz5t%\&,,zDc>_Z}pWLDh.A7";
    
    if(!empty($value))
    {
        $value = rtrim(
                mcrypt_decrypt(
                MCRYPT_RIJNDAEL_256, 
                $secretKey, 
                base64_decode($value), 
                MCRYPT_MODE_ECB,
                mcrypt_create_iv(
                    mcrypt_get_iv_size(
                        MCRYPT_RIJNDAEL_256,
                        MCRYPT_MODE_ECB
                    ), 
                    MCRYPT_RAND
                )
            ), "\0"
        );

        if(@unserialize($value))
        {
            $value = @unserialize($value);
        }
    }

    return $value;
}