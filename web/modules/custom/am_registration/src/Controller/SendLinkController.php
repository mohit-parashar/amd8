<?php

namespace Drupal\am_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SendLinkController extends ControllerBase {

  public function sendMail($user,$link,$user_mail) {

  	// Initialize MailManager
         $mailManager = \Drupal::service('plugin.manager.mail');
         $module = 'am_registration';
         $key = 'send_login_link';
         $to = $user_mail;
         $params = array();
    
     // Initialize token object
         $data = array('user' => $user);
         $token_service = \Drupal::token();

         // Create am_registration config object.
         $config = \Drupal::config('am_registration.settings');

         // Prepare Body and Subject
         $body_message = $token_service->replace($config->get('body'),$data);
         $body_message = str_replace("[user:one-time-login-url]",$link,$body_message);
         $subject = $token_service->replace($config->get('subject'),$data);

//           $params['body'] = <<<EOD
//          <table width="600" border="0" cellpadding="0" cellspacing="0" align="center">
//     <tbody><tr><td height="45"></td></tr>
//     <tr align="center">
//       <td><img class="logo" src="http://creative.wddemo.net/Demo/americanMagzine/logo.png" alt=" " style="width:185px;"></td>
//     </tr>
//     <tr><td height="45"></td></tr>
//     <tr><td height="1" bgcolor="#d8d2cc"></td></tr>
//   </tbody></table>
// EOD;
         $params['body'] = $body_message;
//          $params['body'] .= <<<EOD
//          <table width="600" border="0" cellpadding="0" cellspacing="0" align="center">
//     <tbody><tr><td height="1" bgcolor="#d8d2cc" colspan="3"></td></tr>
//     <tr><td height="43" colspan="3"></td></tr>
//     <tr>
//       <td width="46" class="mobilehidden"></td>
//       <td>
//         <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" style="font-family:Arial, Helvetica, sans-serif;font-size:12px; line-height:18px; color:#8d8d8d;">
//           <tbody><tr>
//             <td>
//               Why am I receiving this message? You recently visited americamagazine.org and requested a log-in link to comment or access one of our other member-only resources. If you didn’t make this request and have received this message by mistake, please disregard.
//             </td>
//           </tr>
//           <tr><td height="25"></td></tr>
//           <tr>
//             <td>
//               <b>© 2017 America Media  |  All Rights Reserved  | Facebook  |  Twitter  |  YouTube</b>
//               <br>
//               106 West 56th St., New York, NY 10019-3803, USA
//               <br>
//               Visit americamagazine.org  |  Email Preferences 
//             </td>
//           </tr>
//         </tbody></table>
//       </td>
//     <td width="46" class="mobilehidden"></td>
//     </tr>
//     <tr><td height="135" colspan="3"></td></tr>

// </tbody></table>
// EOD;
         $params['subject'] = $subject;
         //echo "<pre>";print_r($params);die("sdf");
         $langcode = \Drupal::currentUser()->getPreferredLangcode();
         $send = true;
         $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
         if ($result['result'] !== true) {
           drupal_set_message(t('There was a problem sending your message and it was not sent.'), 'error');
           return new RedirectResponse(\Drupal::url('<front>'));
         }

    return $result;
  }

  public function httpPost($url,$params)
{
  $postData = '';
   //create name value pairs seperated by &
   foreach($params as $k => $v) 
   { 
      $postData .= $k . '='.$v.'&'; 
   }
   $postData = rtrim($postData, '&');
 
    $ch = curl_init();  
 
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_HEADER, false); 
    curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);    
 
    $output=curl_exec($ch);
 
    curl_close($ch);
    return $output;
 
}



}