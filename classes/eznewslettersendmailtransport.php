<?php
//
// Definition of eZewsletterSendmailTransport class
//
// Created on: <10-Dec-2002 14:41:22 amos>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// COPYRIGHT NOTICE: Copyright (C) 1999-2006 eZ systems AS
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//

/*! \file ezsendmailtransport.php
*/

/*!
  \class eZSendmailTransport ezsendmailtransport.php
  \brief Sends the email message to sendmail which takes care of sending the actual message.

  Uses the mail() function in PHP to pass the email to the sendmail system.

*/

class eZNewsletterSendmailTransport extends eZNewsletterMailTransport
{
    /*!
     Constructor
    */
    function __construct()
    {
    }

    /*!
     \reimp
    */
    function sendMail( &$mail )
    {
        $ini = eZINI::instance();
        $emailFrom = $mail->sender();
        $emailSender = $emailFrom['email'];
        if ( !$emailSender || count( $emailSender) <= 0 )
            $emailSender = $ini->variable( 'MailSettings', 'EmailSender' );
        if ( !$emailSender )
            $emailSender = $ini->variable( 'MailSettings', 'AdminEmail' );
        if ( !eZNewsletterMail::validate( $emailSender ) )
            $emailSender = false;
        $isSafeMode = ini_get( 'safe_mode' );
        if ( $isSafeMode and
             $emailSender and
             $mail->sender() == false )
        {
            $mail->setSenderText( $emailSender );
        }
        $message = $mail->body();
        $extraHeaders = $mail->headerText( array( 'exclude-headers' => array( 'To', 'Subject', 'content-transfer-encoding',  'content-disposition' ) ) );

        $sendMailSettings = eZINI::instance( 'ezsendmailsettings.ini' );
        $sendMailParameter = $sendMailSettings->variable( 'SendNewsletter', 'MTAEnvelopeReturnPathParameter' );

        if ( $isSafeMode or
             !$emailSender )
        {
            return mail( $mail->receiverEmailText(), $mail->subject(), $message, $extraHeaders );
        }
        else
        {
            return mail( $mail->receiverEmailText(), $mail->subject(), $message, $extraHeaders, $sendMailParameter . ' -f' . $emailSender );
        }
    }
}

?>
