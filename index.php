<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <meta http-equiv="content-type" content="text/html; charset=utf-8" />
<?php
if (empty($_GET['queue'])) 
    $queue="";
else {
    $queue=$_GET['queue'];
}
echo "<meta http-equiv='refresh' content='3;url=index.php?queue=$queue'>";
?>
 <title>Статистика очереди</title>

  <style type="text/css">
  td.large {
  color:red;
  text-align:center;
  font-size:36pt;
  }
  </style>

  <style type="text/css">
  td.medium {
  color:black;
  text-align:center;
  font-size:18pt;
  }
  </style>

  <style type="text/css">
  td.mediuml {
  color:black;
  text-align:left;
  font-size:18pt;
  white-space: nowrap;
  }
  </style>

  <style type="text/css">
  td.mediumq {
  color:black;
  background-color:#FA5858;
  text-align:center;
  font-size:10pt;
  }
  </style>


  <style type="text/css">
  tr.heading {
  color:blue;
  text-align:center;
  font-size:18pt;
  }
  </style>

  <style type="text/css">
  tr.heading-medium {
  color:blue;
  text-align:center;
  font-size:16pt;
  white-space: nowrap;
  }
  </style>

</head>
 <body>

<?php
  require_once('./phpagi/phpagi-asmanager.php');

if (empty($_GET['queue'])) 
    $queue="";
else {
    $queue=$_GET['queue'];
}


$myfile = './queues.conf';
echo "<form id='queue' action='index.php' method='GET'>";

$lines = file($myfile);
 echo "<a href=\"index.php\">Выбор очереди</a> - ";
foreach($lines as $queues){
 if (preg_match("/^\[/i", $queues)) {
 echo "<button name='queue' type='submit' value='".substr($queues, 1, -2)."'>".substr($queues, 1, -2)."</button>";
 }
}

echo "</form>";
echo "<br />";

if ($queue) {

  $asm = new AGI_AsteriskManager();
  if($asm->connect())
  {
    $result = $asm->Command("queue show $queue");
    $cresult = $asm->Command("core show channels");

// COUNT AVAILABLE AGENTS

   $n = 0;

    if(!strpos($result['data'], ':'))
      echo $peer['data'];
    else
    {
      $data = array();
      foreach(explode("\n", $result['data']) as $line)
      {
         if (preg_match("/Local/i", $line)) {
          $n = $n + 1;
         }
         if (preg_match("/SIP/i", $line)) {
          $n = $n + 1;
         }
      }
    }


// ECHO THE QUEUE STATUS FIRST

    if(!strpos($result['data'], ':'))
      echo $peer['data'];
    else
    {
      $data = array();
      echo "<table border='1'; cellpadding=6pt;>";
      echo "<tr class='heading';><td>Номер очереди</td><td>Ожидающих в очереди</td><td>Всего операторов</td><td>Обработано вызовов</td><td>Пропущено вызовов</td><td>Среднее время ожидания, сек</td><td>Среднее время разговора,сек</td></tr>";
      foreach(explode("\n", $result['data']) as $line)
      {
         if (preg_match("/talktime/i", $line)) {
          echo "<tr>";
          $pieces = explode(" ", $line);
          echo "<td class='large';>$pieces[0] </td>";
          echo "<td class='large';>$pieces[2] </td> ";
          echo "<td class='large';>$n </td> ";
          echo "<td class='large';>".trim($pieces[14], "C:,")."</td> ";
          echo "<td class='large';>".trim($pieces[15], "A:,")."</td> ";
          echo "<td class='large';>".trim($pieces[9], "(s")." </td> ";
          echo "<td class='large';>".trim($pieces[11], "s")." </td> ";
          echo "</tr>";
         }
      }
      echo "</table>";
    }


   echo "<br /><table align=left border='0'>";

// ECHO THE CALLS WAITING
   echo "<tr><td width=300px align=left valign=top>";

   echo "<h3><u>Ожидающие в очереди</u></h3>";

    if(!strpos($result['data'], ':'))
      echo $peer['data'];
    else
    {
      $data = array();
      echo "<table border='1'; cellpadding=6pt;>";
      echo "<tr class='medium';><td>Позиция</td><td>Время ожидания</td></tr>";
      foreach(explode("\n", $result['data']) as $line)
      {

         if (preg_match("/wait/i", $line)) {
          $pieces2 = explode(" ", $line);
          echo "<tr>";
          echo "<td class='mediumq';>".trim($pieces2[6], ".")." </td> ";
          echo "<td class='mediumq';>".trim($pieces2[9], ",")." </td> ";
          echo "</tr>";
      }

      }
      echo "</tr></table>";
    }



   echo "</td><td width=600px valign=top>";

// ECHO AGENTS

   echo "<h3><u>Доступные операторы</u></h3>";

    if(!strpos($result['data'], ':'))
      echo $peer['data'];
    else
    {
      $data = array();
      echo "<table border='1'; cellpadding=6pt;>";
      echo "<tr class='heading-medium';><td>Оператор</td><td>Последний вызов, сек</td><td>Вызовов сегодня</td></tr>";
      foreach(explode("\n", $result['data']) as $line)
      {
         if (preg_match("/Not in use/i", $line)) {
          if (preg_match("/SIP|Local/i", $line)) {
           if (!preg_match("/paused/i", $line)) {
           $pieces2 = explode(" ", $line);
           echo "<tr bgcolor=#82FA58>";
           echo "<td class='medium';> ".trim($pieces2[6], "(")." </td> ";
           if(!empty($pieces2[25]) and is_numeric($pieces2[25]) and preg_match("/secs/i", $pieces2[26])) {
        	$c1 = $pieces2[25];
        	}
	   else {
        	if(preg_match("/Local/i", $pieces2[6]) and !empty($pieces2[18]) and is_numeric($pieces2[18])) $c1 = $pieces2[18]; else $c1 = "?";
        	if(preg_match("/SIP/i", $pieces2[7]) and !empty($pieces2[24]) and is_numeric($pieces2[24]) and preg_match("/secs/i", $pieces2[25])) $c1 = $pieces2[24]; else $c1 = "?";
           }
           if(!empty($pieces2[22]) and is_numeric($pieces2[21]) and !empty($pieces2[21]) and preg_match("/calls/i", $pieces2[22])) {
        	 $c2 = $pieces2[21];
        	}
	   else {
        	if(preg_match("/Local/i", $pieces2[6]) and is_numeric($pieces2[14]) and !empty($pieces2[14])) $c2 = $pieces2[14]; else $c2 = "?";
                if(preg_match("/SIP/i", $pieces2[7]) and !empty($pieces2[20]) and is_numeric($pieces2[20]) and preg_match("/calls/i", $pieces2[21])) $c2 = $pieces2[20]; else $c2 = "?";
           }
           echo "<td class='medium';>$c1 </td> ";
           echo "<td class='medium';>$c2 </td> ";
           echo "</tr>";
           }
          }
         }
      }
      echo "</table>";
    }

// ECHO AGENTS BUSY

   echo "</td><td width=600px valign=top>";

   echo "<h3><u>Занятые операторы</u></h3>";

    if(!strpos($result['data'], ':'))
      echo $peer['data'];
    else
    {
      $data = array();
      echo "<table border='1'; cellpadding=6pt;>";
      echo "<tr class='heading-medium';><td>Оператор</td></tr>";
      foreach(explode("\n", $result['data']) as $line)
      {
         if (preg_match("/Not in use/i", $line)) {
          if (preg_match("/SIP|Local/i", $line)) {
           if (!preg_match("/paused/i", $line)) {
          $pieces2 = explode(" ", $line);
          echo "<tr bgcolor=#FA8258>";
          $pieces3 = array();
          if(!strpos($cresult['data'], "SIP/"))
             echo $peer['data'];
          else
          {
             $data = array();
             foreach(explode("\n", $cresult['data']) as $cline)
             {
                if (preg_match("/SIP|Local/i", $cline)) {
                   $cline = preg_replace('|\s+|', ' ', $cline);
                   $pieces3 = explode(" ", $cline);
                   $sipch=strpos($pieces3[0], "-");
                   $pieces3[0]=substr($pieces3[0], 0, $sipch);
                   if (preg_match("/UP|Ring/i", $pieces3[2])) {
                      if (preg_match("/SIP/i", $pieces2[7])) {
                          $chann = trim($pieces2[7], "(");
                          if ($chann == $pieces3[0]) {
                              echo "<td class='medium';> ".trim($pieces2[6], "(")." </td> ";
                              echo "</tr>";
                          }
                      }
                      if (preg_match("/LocalSIP/i", $pieces2[7])) {
                          $chann = trim($pieces2[7], "(");
                          if ($chann == $pieces3[0]) {
                              echo "<td class='medium';> ".trim($pieces2[6], "(")." </td> ";
                              echo "</tr>";
                          }
                      }
                    }
                  }
                }
              }
            }
           }
          }
        }
      }
  echo "</table>";
}

// ECHO AGENTS PAUSE

echo "</td><td width=600px valign=top>";

   echo "<h3><u>Операторы на паузе</u></h3>";

    if(!strpos($result['data'], ':'))
      echo $peer['data'];
    else
    {
      $data = array();
      echo "<table border='1'; cellpadding=6pt;>";
      echo "<tr class='heading-medium';><td>Оператор</td></tr>";
      foreach(explode("\n", $result['data']) as $line)
      {
         if (preg_match("/Not in use/i", $line)) {
           if (preg_match("/paused/i", $line)) {
           $pieces2 = explode(" ", $line);
           echo "<tr bgcolor=#FA58F4>";
           echo "<td class='medium';> ".trim($pieces2[6], "(")." </td> ";
           echo "</tr>";
           }
         }
      }
      echo "</table>";
    }

echo "</td></tr></table>";

    $asm->disconnect();
  }
?>

</body>
</html>
