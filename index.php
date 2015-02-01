<p>
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// ********************************CONFIGURATION SETTINGS**************************************
// you can change the config settings in this section

// set the folder identifier. This reg exp will ber used to identify folder logic in cases where the file name itself does not
// identify the file to the system but rather the parent folder has the the id number
$folder_id_str="/\(ID-\d+\)/i";
preg_match("/\(ID-\d+\)/i", "cswirl(Id-976)", $output_array);

// populate this array with the path to folders on the network where you want to get files from
// this will usually be areas where devices such as xray machines are dumping files
$source_directories  = array(
'C:/Users/paolo.ATLANTICPACIFIC/Documents/apl_test_records',
'C:/Users/paolo.ATLANTICPACIFIC/Documents/apl_test_records2',
'C:/Users/paolo.ATLANTICPACIFIC/Documents/apl_test_records3');

// populate this array with any device folders on the network
// these will be folders that are bee free device folders on the local network and have google drive sync tool pointed to them
$target_directories = array(
'C:/Users/paolo.ATLANTICPACIFIC/Documents/2013',
'C:/Users/paolo.ATLANTICPACIFIC/Documents/2014',
'C:/Users/paolo.ATLANTICPACIFIC/Documents/BAS',
'C:/Users/paolo.ATLANTICPACIFIC/Documents/bookkeeping');

// set mode to 'copy' if file copy is required else the file will be moved. Copy is Useful for testing and data migrations
$mode="xcopy";

// for added security, if allow use of a 'trash' directory if "" then no thash
$trash_dir="C:/Users/paolo.ATLANTICPACIFIC/Documents/autofile_trashcan";

// *******************************************************************************************

function scan_dir($directory)
    {
    global $directory_tree;
    // pl trying to rewrite the above function


    $directory_list = opendir($directory);
    // and scan through the items inside
	while (FALSE !== ($file = readdir($directory_list)))
        {
        if($file != '.' && $file != '..')
            {
            $path = $directory.'/'.$file;
                        if(is_dir($path))
                    {
                    scan_dir($path);
                    }
                    elseif(is_file($path))
                       {
                       $directory_tree[]=$path;
                       }
             }

        }//end while
    return $directory_tree;
    }//end function

function tidy_foldername($filename)
    {
    $filename=trim(strtoupper($filename),")(");
    return $filename;
    }//end function

function get_file_name_only($file_path)
    {
    $path_parts = pathinfo($file_path);
    return $path_parts['basename'];
    }

echo "Mode= $mode. If mode is not 'copy' the source files will be deleted<br>\n";

echo "Scanning for device folders:<br>\n";
foreach($target_directories as $target_dir) {
   $split_path_arr=explode('/',$target_dir);
   $device_name =  $split_path_arr[count($split_path_arr)-1];
   $device_names_arr[$device_name]=$device_name;
   // $device_path_arr[$device]=$target_dir;
   if(!file_exists($target_dir) || !is_dir($target_dir)) {
       echo "ERROR: Target folder for device:".$device_name." NOT found at $target_dir\n<br>";
       }
       else {
          echo "Target folder for device:".$device_name." found at $target_dir\n<br>";
          $devices_arr[$device_name]=$target_dir;
          }
   }

echo "\n<br>Scanning for files in source folders ".$source_dir.":<br>\n";
foreach($source_directories as $source_dir) {
if(!file_exists($source_dir) || !is_dir($source_dir)) {
          echo "ERROR: Source folder NOT found at $source_dir\n<br>";
          }
          else {
          $file_names=scan_dir($source_dir);
          echo "scanning ".$source_dir."<br>";
          }
    }
echo "<br><br>The following files have been found:<br>\n";

// move the files
foreach($file_names as $file_path)
   {
   echo "_____________________________<br>\nFound:".$file_path."<br>";
   foreach($device_names_arr as $devicelist)
      {
      if (stristr($file_path,"/".$devicelist))
         {
         if(is_dir($devices_arr[$devicelist]))
             {
             echo $file_path." move to device:".$devicelist." located at:".$devices_arr[$devicelist]."<br>\n";
             // now we need to incorporate whole folder logic
             //if (stristr($file_path,$folder_id_str))
             if (   preg_match("/\(ID-\d+\)/i", $file_path, $output_array) )
                 {
                 echo "This file ".get_file_name_only($file_path)." will be moved with a folder because the path contains ". $output_array[0].". The folder will be called: ".tidy_foldername($output_array[0])."<br>\n";
                 if (     !is_dir($devices_arr[$devicelist]."/".tidy_foldername($output_array[0]))  )
                    mkdir ( $devices_arr[$devicelist]."/".tidy_foldername($output_array[0]) );
                 }
                 else
                    echo "This file will be moved as a file only - note the file name should contain an ID number<br>\n";

             if (!copy($file_path, $devices_arr[$devicelist]."/".tidy_foldername($output_array[0])."/".get_file_name_only($file_path)))
                    echo "failed to copy $file...<br>\n";
                       else
                          {
                          echo "Copy successfull<br>\n";
                          if($mode!="copy")
                              {
                              echo "Will now delete the source file<br>\n";
                              if($trash_dir!='')
                                  {
                                  if(!is_dir($trash_dir))
                                      mkdir($trash_dir);
                                  copy($file_path,$trash_dir."/".get_file_name_only($file_path));
                                  }
                              if(  !unlink($file_path)  )
                                  echo "failed to delete the file<br>\n";
                              }
                          }


             }
             else
                echo "No target directory found<br>\n";
         }
      }

   }




echo "<br><br><br>";


?>
</p>