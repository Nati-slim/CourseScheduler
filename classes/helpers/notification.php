<?php
  // The email address notifications should be sent to
  $____email_address = 'contact@twitapps.com';
 
  // This array maps incoming emails to scripts
  $____processor_map = array(
    // For the ta_follows user there's a separate script for each notification type
    'ta_follows' => array(
      'is_following' => dirname(__FILE__).'/ta_follows/new_follower.php',
      'direct_message' => dirname(__FILE__).'/ta_follows/new_dm.php',
    ),
    // For this user one script handles all notifications
    'ta_replies' => dirname(__FILE__).'/ta_replies/process_email.php',
  );
  
  // CONFIGURATION ENDS - NOTHING BELOW THIS LINE SHOULD NEED CUSTOMISATION
 
  // OUTPUT HANDLER
  // It's important that this script doesn't output anything, so this chunk of code emails it somewhere instead
  ob_start('EmailOutput');
  register_shutdown_function('EmailOutput');
  function EmailOutput($str = false)
  {
    static $output = '';
    if ($str === false)
    {
      mail($GLOBALS['____email_address'], 'Output from incoming_mail.php', $output, 'From: Incoming Email <'.$GLOBALS['____email_address'].'>', '-f'.$GLOBALS['____email_address']);
      $output = '';
    }
    else
    {
      $output .= $str;
    }
  }
  // END OF OUTPUT HANDLER
 
  // Get the email content
  $____data = file_get_contents('php://stdin');
  
  // Parse it
  $____msg = mailparse_msg_create();
  if (!mailparse_msg_parse($____msg, $____data))
  {
    echo "Failed to parse message\n\n".$____msg;
  }
  else
  {
    // Get the bits we need
    $____message = mailparse_msg_get_part($____msg, 1);
    $info = mailparse_msg_get_part_data($____message);
    
    if (!$____message or !$info)
    {
      echo "Failed to get message or info\n\n".$____msg;
    }
    else
    {
      $headers = array();
      $____prefix = 'x-twitter';
      $____prefixlen = strlen($____prefix);
      foreach ($info['headers'] as $____key => $____val)
      {
        $____key = strtolower($____key);
        if (substr($____key, 0, $____prefixlen) == $____prefix)
        {
          $headers[substr($____key, $____prefixlen)] = $____val;
        }
      }
 
      // Make sure it's a message from Twitter
      if (count($headers) == 0)
      {
        echo "Missing required headers\n\n".$____msg;
      }
      else
      {
        // Now get the message body and clean it up a bit
        ob_start();
        mailparse_msg_extract_part($____message, $____data);
        $body = ob_get_clean();
            $body = urldecode($body);
        if (!empty($info['charset'])) $body = iconv($info['charset'], 'UTF-8', $body);
            $body = html_entity_decode($body, ENT_NOQUOTES, 'UTF-8');
        
        // Now attempt to find a handler for this notification
        $____handler = false;
        // First do we handle this user at all?
        if (isset($____processor_map[$headers['recipientscreenname']]))
        {
          // Is it a single script or one per type?
          if (is_array($____processor_map[$headers['recipientscreenname']]))
          {
            // Do we have one for this specific type?
            if (isset($____processor_map[$headers['recipientscreenname']][$headers['emailtype']]))
            {
              // Set the handler
              $____handler = $____processor_map[$headers['recipientscreenname']][$headers['emailtype']];
            }
          }
          elseif (is_string($____processor_map[$headers['recipientscreenname']]))
          {
            // Single script, set the handler
            $____handler = $____processor_map[$headers['recipientscreenname']];
          }
        }
        
        // Did we find a handler?
        if ($____handler === false)
        {
          echo "No appropriate handler found\n\n".$____msg;
        }
        // Does it exist?
        elseif (!file_exists($____handler))
        {
          echo "Handler found but file missing: '".$____handler."'\n\n".$____msg;
        }
        // All good so do it!
        else
        {
          // We catch output so we can pre and postfix it with useful info
          ob_start();
          require $____handler;
          $____output = ob_get_clean();
          if (strlen(trim($____output)) > 0)
          {
            // Output caught, say it was from the handler script and include the raw message after it
            echo "Output from handler: '".$____handler."'...\n\n".$____output."\n\n".$____msg;
          }
        }
      }
    }
  }
  mailparse_msg_free($____msg);
?>
