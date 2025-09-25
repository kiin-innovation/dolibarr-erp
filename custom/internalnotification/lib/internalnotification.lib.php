<?php

/* Copyright (C) 2015 Gilles Lengy / Artaban Communication <gilles.lengy@artaban.fr>
 * Copyright (C) 2015 Gilles Dumont / Artaban Communication <gilles@artaban.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
// **** INIT ****
require_once(DOL_DOCUMENT_ROOT . "/core/lib/functions.lib.php");
require_once(DOL_DOCUMENT_ROOT . "/core/class/commonobject.class.php");
require_once(DOL_DOCUMENT_ROOT . "/core/class/CMailFile.class.php");
require_once(DOL_DOCUMENT_ROOT . "/core/lib/files.lib.php");
require_once(DOL_DOCUMENT_ROOT . "/societe/class/societe.class.php");
require_once(DOL_DOCUMENT_ROOT . "/contact/class/contact.class.php");
require_once(DOL_DOCUMENT_ROOT . "/user/class/user.class.php");
require_once(DOL_DOCUMENT_ROOT . "/comm/action/class/actioncomm.class.php");
dol_include_once("./internalnotification/class/internalnotification.class.php");

/**
 * Prepare array with list of tabs
 *
 * @param Object $object Object related to tabs
 * @return  array                Array of tabs to show
 */
function internalnotification_prepare_head()
{

    global $langs, $conf, $user;
    $langs->load("internalnotification@internalnotification");

    $h = 0;
    $head = array();
    if ($conf->societe->enabled) {
        $head[$h][0] = dol_buildpath("/internalnotification/admin/internalnotification.php?tab=thirdparties", 1);
        $head[$h][1] = $langs->trans("ThirdParties");
        $head[$h][2] = 'thirdparties';
        $h++;

        $head[$h][0] = dol_buildpath("/internalnotification/admin/internalnotification.php?tab=contactsaddresses", 1);
        $head[$h][1] = $langs->trans("ContactsAddresses");
        $head[$h][2] = 'contactsaddresses';
        $h++;
    }
    if ($conf->agenda->enabled) {
        $head[$h][0] = dol_buildpath("/internalnotification/admin/internalnotification.php?tab=events", 1);
        $head[$h][1] = $langs->trans("Events");
        $head[$h][2] = 'events';
        $h++;
    }

    return $head;
}

/**
 * Prepare set of elements for a particular tab
 *
 * @param TAB $tab ( Action that trigger the notification )
 * @return array() The first element of the array is the title of the tab ( wich can be an error message to be displayed ( default )
 *                 The other elements are the availables trigger action
 */
function elements_for_internal_notification($tab = '')
{

    global $langs, $conf, $user;

    $elements = array();

    switch ($tab) {
        case 'thirdparties':
            if ($conf->societe->enabled) {
                $elements[] = 'COMPANY_CREATE';
                $elements[] = 'COMPANY_MODIFY';
                $elements[] = 'COMPANY_DELETE';
            }
            break;
        case 'contactsaddresses':
            if ($conf->societe->enabled) {
                $elements[] = 'CONTACT_CREATE';
                $elements[] = 'CONTACT_MODIFY';
                $elements[] = 'CONTACT_DELETE';
                $elements[] = 'CONTACT_ENABLEDISABLE';
            }
            break;
        case 'events':
            if ($conf->agenda->enabled) {
                $elements[] = 'ACTION_CREATE';
                $elements[] = 'ACTION_MODIFY';
                $elements[] = 'ACTION_DELETE';
            }
            break;
        default:
            $elements['ErrorNoSetOfForms'] = $langs->trans("ErrorNoSetOfForms");
    }

    return $elements;
}

/**
 * Prepare set of forms form a particular tab
 *
 * @param Action $trigger_action ( Action that trigger the notification )
 * @param Action $errors ( if >0, then, there is at least one error in the form submission... use to define the value of the field... Either the original value or the value submitted by the form )
 * @param Action $receiver_email
 * @param Action $subject
 * @param Action $body
 * @return  bool
 */
function form_for_internal_notification($trigger_action, $errors = 0, $receiver_email = '', $subject = '', $body = '', $checkbox_1 = 'false')
{

    global $langs, $conf, $db; //, $user;

    $notification_template_title = $langs->trans("TitleTemplateNotification_" . $trigger_action); // internalnotifications\langs\fr_FR\internalnotifications.lang

    $object_notification = new Internalnotification($db);
    $object_notification->fetch($trigger_action, '', true);
    $receiver_email_form = ($errors > 0 ? $receiver_email : $object_notification->receiver_email);
    $subject_form = ($errors > 0 ? $subject : $object_notification->subject);
    $body_form = ($errors > 0 ? $body : $object_notification->body);
    $checkbox_1 = ($errors > 0 ? $checkbox_1 : $object_notification->checkbox_1);

    switch ($trigger_action) {
        // Compagny
        case 'COMPANY_CREATE':
        case 'COMPANY_MODIFY':
        case 'COMPANY_DELETE':
            $tab = 'thirdparties';
            break;
        // Contact
        case 'CONTACT_CREATE':
        case 'CONTACT_MODIFY':
        case 'CONTACT_DELETE':
        case 'CONTACT_ENABLEDISABLE':
            $tab = 'contactsaddresses';
            break;
        // Action (events)
        case 'ACTION_MODIFY':
        case 'ACTION_CREATE':
        case 'ACTION_DELETE':
            $tab = 'events';
            break;
    }

    print '<form name="form" action="' . $_SERVER["PHP_SELF"] . '" method="post" enctype="multipart/form-data">';

    print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
    print '<input type="hidden" name="trigger_action" value="' . $trigger_action . '">';
    print '<input type="hidden" name="tab" value="' . $tab . '">';
    print '<input type="hidden" name="action" value="modify_template">';

    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre"><th class="liste_titre">';
    print $notification_template_title;
    print '</th></tr>';
// You can use here results label_lang
    print '<tr><td class="pair">';
    print '<table class="border" width="100%">';
    print '<tr><td width="25%" class="fieldrequired">' . $langs->trans("MailDestinataireNotification") . '</td><td><input class="flat" name="receiver_email" size="40" value="' . $receiver_email_form . '"></td></tr>';

    if ($tab == "thirdparties") {
        $checkbox_1_checked = ($checkbox_1 == 'true' ? 'checked' : '');
        print '<tr><td width="25%">' . $langs->trans("NotifyCommercial") . '</td>';
        /*        if ($trigger_action != "COMPANY_CREATE") {
                    print '<td><input type="checkbox" name="checkbox_1" value="true" ' . $checkbox_1_checked . ' ></td></tr>';
                } else {
                    print '<td>' . $langs->trans("NotifyCommercialNotPossible") . '</tr>';
                }*/
        print '<td><input type="checkbox" name="checkbox_1" value="true" ' . $checkbox_1_checked . ' ></td></tr>';
    }
    if ($tab == "events") {
        $checkbox_1_checked = ($checkbox_1 == 'true' ? 'checked' : '');
        print '<tr><td width="25%">' . $langs->trans("NotifyUsersAndContacts") . '</td><td><input type="checkbox" name="checkbox_1" value="true" ' . $checkbox_1_checked . ' ></td></tr>';
    }
    print '</table>';
    print '</br>';


    print '<table class="border" width="100%">';
    print '<tr><td width="25%" class="fieldrequired">' . $langs->trans("SubjectNotification") . '</td><td><input class="flat" name="subject" size="60" value="' . $subject_form . '"></td></tr>';
    print '<tr><td width="25%" valign="top">';
    print '<br><i>' . $langs->trans("SubstitutionTags") . ':<br>';

    if (strpos($trigger_action, 'COMPANY') !== false) {
        print '__THIRDPARTY_NAME__ = ' . $langs->trans('ThirdpartyName') . '<br>';
        print '__THIRDPARTY_COM_NAMES__ = ' . $langs->trans('ThirdpartyComNames') . '<br>';
        print '__THIRDPARTY_COM_EMAILS__ = ' . $langs->trans('ThirdpartyComEmails') . '<br>';
    }
    if (strpos($trigger_action, 'CONTACT') !== false) {
        print '__CONTACT_FIRSTNAME_LASTNAME__ = ' . $langs->trans('ContactFirstnameLastname') . '<br>';
    }
    if (strpos($trigger_action, 'ACTION') !== false) {
        print '__EVENT_TITLE__ = ' . $langs->trans('EventTitle') . '<br>';
        print '__EVENT_LOCATION__ = ' . $langs->trans('EventLocation') . '<br>';
        print '__EVENT_DATE_BEGIN__ = ' . $langs->trans('EventDateBegin') . '<br>';
        print '__EVENT_DATE_END__ = ' . $langs->trans('EventDateEnd') . '<br>';
        if (strpos($trigger_action, 'DELETE') === false) {
            print '__EVENT_DESCRIPTION__ = ' . $langs->trans('EventDescription') . '<br>';
        }
    }
    if (strpos($trigger_action, 'DELETE') === false) {
        if ($tab == 'events') {
            print '__EVENT_ASSIGNED_TO__ = ' . $langs->trans('EventAssignedTo') . '<br>';
        }
        print '__LINK__ = ' . $langs->trans('Link') . '<br>';
    }
    print '__USER_FIRSTNAME_LASTNAME__ = ' . $langs->trans('UserFisrtnameLastname') . '<br>';
    print '</i></td>';
    print '<td>';
    print '<textarea name="body" rows="8" cols="72" class="flat">';
    print $body_form;
    print '</textarea>';
    print '</td></tr>';
    print '</table>';
    /*
     * Actions barr
     *
     */
    print '<div class="tabsAction">';

    if ($object_notification->id > 0) {
        print '<input class="butAction" type="submit" name="delete_notification" value="' . $langs->trans("DeleteNotificationTemplate") . '">';
        print '<input class="butAction" type="submit" value="' . $langs->trans("ModifyNotificationTemplate") . '">';
    } else {
        print '<input class="butAction" type="submit" value="' . $langs->trans("CreateNotificationTemplate") . '">';
    }
    print "</div>";
    print '</td></tr>';
    print '</table>';

    print "</form>\n";
    print '<br />';
    print '<br />';

    return true;
}

/**
 *    sendNotification
 * @param object $object The object
 * @param Action $action Action that is triggered
 * @param User $user User that triggered the action
 * @return OK > 0, KO < 0
 */
function sendNotification($object, $action, $user)
{

    //print $action;
    //die();

    global $conf, $langs, $db;
    //$langs->load("supportparutionnotification@supportparutionnotification");
    $return_mail_errors = 0;
    $objet_notification = new Internalnotification($db);
    $id = $object->id;

    /****
     * Gathering informations
     **********************************************************************/
    $ref = '';
    $objet_notification->fetch($action, $ref, true);
    //var_dump($objet_notification);
    //print " | " . $objet_notification->id;

    if ($objet_notification->id > 0) {
        $contact = new contact($db);
        $userAssigned = new user($db);
        $userCommercialThirdParty = new user($db);
        $contactEnvoi = false;
        $emailContact = "";
        $userCommercialThirdPartyNames = "";
        $userCommercialThirdPartyEmails = "";
        $sendto = $objet_notification->receiver_email;
        $sendto_array = array($sendto);
        //$sendto_array = array();
        $subject = $objet_notification->subject;
        $body = $objet_notification->body;
        $checkbox_1 = $objet_notification->checkbox_1;
        $username = $user->firstname . ' ' . $user->lastname;
        $userMail = $user->email;
        if ($userMail != "") {
            $from = $userMail;
        } else {
            $from = $conf->global->MAIN_MAIL_EMAIL_FROM;
        }
        //eve
        $datep = "";
        $datef = "";
        $event_assigned_to = "";
        $note = "";

        if (isset($conf->global->MAIN_MAIL_ERRORS_TO)) {
            $errorsto = $conf->global->MAIN_MAIL_ERRORS_TO;
        } else {
            $errorsto = getDolGlobalString('MAIN_MAIL_ERRORS_TO');
        }


        switch ($action) {
            // Compagny ( Organisme )
            case 'COMPANY_CREATE':
            case 'COMPANY_MODIFY':
            case 'COMPANY_DELETE':
                $organismenom = $object->name;
                $link = DOL_MAIN_URL_ROOT . '/societe/card.php?socid=' . $id;
                if ($action == "COMPANY_CREATE") {

                    /*                    $myArray = json_decode(json_encode($object), true);
                    */
                    $commercialIdArray = GETPOST('commercial', 'array');

                    /* array_walk_recursive($myArray, function ($value, $key) use (&$myArrayString) {
                         if (!isset($myArrayString) || $myArrayString == "") {
                             $myArrayString = " xxxx ";
                         }
                         $myArrayString .= $key . ' : ' . $value . ' xxxx ';
                     }, $myArrayString);*/

                    $i = 0;
                    foreach ($commercialIdArray as $value) {

                        $c = new user($db);

                        $c->fetch((int)$value);

                        if ($i > 0) {
                            $userCommercialThirdPartyNames .= ", ";
                            $userCommercialThirdPartyEmails .= ", ";
                        }
                        $userCommercialThirdPartyNames .= $c->firstname . " " . $c->lastname;
                        $userCommercialThirdPartyEmails .= $c->email;
                        if ($checkbox_1 == 'true') {
                            if ($c->email != "") {
                                array_push($sendto_array, $c->email);
                            }
                        }
                        $i++;
                    }

                }

                if ($action == "COMPANY_MODIFY" || $action == "COMPANY_DELETE") {
                    $commercialArray = $object->getSalesRepresentatives($user);
                    $i = 0;
                    foreach ($commercialArray as $value) {
                        //print $value["lastname"] . " | ";
                        if ($i > 0) {
                            $userCommercialThirdPartyNames .= ", ";
                            $userCommercialThirdPartyEmails .= ", ";
                        }
                        $userCommercialThirdPartyNames .= $value["firstname"] . " " . $value["lastname"];
                        $userCommercialThirdPartyEmails .= $value["email"];
                        if ($checkbox_1 == 'true') {
                            if ($value["email"] != "") {
                                array_push($sendto_array, $value["email"]);
                            }
                        }
                        $i++;
                    }
                }

                $actionSurObjet = "tiers";
                break;
            // Contacts
            case 'CONTACT_CREATE':
            case 'CONTACT_MODIFY':
            case 'CONTACT_DELETE':
            case 'CONTACT_ENABLEDISABLE':
                $contactname = $object->firstname . ' ' . $object->lastname;
                $link = DOL_MAIN_URL_ROOT . '/contact/card.php?id=' . $id;
                $actionSurObjet = "contact";
                break;
            // Action (events)            
            case 'ACTION_CREATE':
            case 'ACTION_MODIFY':
            case 'ACTION_DELETE':
//                print " | action switch  $action<br />";
//               var_dump($object->socpeopleassigned);
//                print " | action switch  $action<br />";

                $eventtitle = $object->label;
                $location = $object->location;
                if ($object->datep) {
                    $datep = $object->datep;
                }
                if ($object->datef) {
                    $datef = $object->datef;
                }
                //$note = strip_tags(br2nl(GETPOST('note', 'restricthtml')));
                $note = strip_tags(br2nl(GETPOST('note', 'none')));
                //$note = iconv(mb_detect_encoding($note, mb_detect_order(), true), "UTF-8", $note);
                $note = html_entity_decode($note, ENT_QUOTES | ENT_HTML5, 'UTF-8');
//                var_dump($note);
//                die();


                $link = DOL_MAIN_URL_ROOT . '/comm/action/card.php?id=' . $id;

                $userIdAssignedArray = $object->userassigned;
                foreach ($userIdAssignedArray as $key => $value) {
                    $userAssigned->fetch($key);
                    //$userAssigned->email . ', ';
                    $event_assigned_to .= $userAssigned->firstname . ' ' . $userAssigned->lastname . ', ';
                }
                $event_assigned_to = trim($event_assigned_to, ', ');

                if ($checkbox_1 == 'true') {

                    // Mails tiers
                    if ($object->socpeopleassigned && !$contactEnvoi) {
                        $contactIdAssignedArray = $object->socpeopleassigned;
                        //var_dump($soc_email_array);
                        foreach ($contactIdAssignedArray as $key => $value) {
                            $contact->fetch($key);
                            $emailContact = $contact->email;
                            if (strpos($emailContact, '@') !== false) {
                                array_push($sendto_array, $emailContact);
                            }
                        }
                    }

                    foreach ($userIdAssignedArray as $key => $value) {
                        $userAssigned->fetch($key);
                        $userMail = $userAssigned->email;
                        array_push($sendto_array, $userMail);
                    }
                }
                break;
        }

        /**********************
         * PREPARATION DU MAIL
         **************************************************/

// Array of possible substitutions (See also fie mailing-send.php that should manage same substitutions)

        $substitutionarray = array();
        if (isset($username)) {
            $substitutionarray += ['__USER_FIRSTNAME_LASTNAME__' => $username];
        }
        if (isset($organismenom)) {
            $substitutionarray += ['__THIRDPARTY_NAME__' => $organismenom];
        }
        if (isset($userCommercialThirdPartyNames)) {
            $substitutionarray += ['__THIRDPARTY_COM_NAMES__' => $userCommercialThirdPartyNames];
        }
        if (isset($userCommercialThirdPartyEmails)) {
            $substitutionarray += ['__THIRDPARTY_COM_EMAILS__' => $userCommercialThirdPartyEmails];
        }
        if (isset($contactname)) {
            $substitutionarray += ['__CONTACT_FIRSTNAME_LASTNAME__' => $contactname];
        }
        if (isset($eventtitle)) {
            $substitutionarray += ['__EVENT_TITLE__' => $eventtitle];
        }
        if (isset($link)) {
            $substitutionarray += ['__LINK__' => $link];
        }
        if (isset($location)) {
            $substitutionarray += ['__EVENT_LOCATION__' => $location];
        }
        if (isset($note)) {
            $substitutionarray += ['__EVENT_DESCRIPTION__' => $note];
        }
        if (isset($event_assigned_to)) {
            $substitutionarray += ['__EVENT_ASSIGNED_TO__' => $event_assigned_to];
        }
//        var_dump($datep);
//        var_dump($datef);
//        die();
        if (is_numeric($datep)) {
            $substitutionarray += ['__EVENT_DATE_BEGIN__' => date("d/m/Y H:i", $datep)];
        } else {
            $substitutionarray += ['__EVENT_DATE_BEGIN__' => ""];
        }
        if (is_numeric($datef)) {
            $substitutionarray += ['__EVENT_DATE_END__' => date("d/m/Y H:i", $datef)];
        } else {
            $substitutionarray += ['__EVENT_DATE_END__' => ""];
        }
        complete_substitutions_array($substitutionarray, $langs);
        $newsubject = make_substitutions($subject, $substitutionarray);
        $newmessage = make_substitutions($body, $substitutionarray);
// Send mail
        $msgishtml = 0;
        foreach ($sendto_array as $sendto) {
//            print "$sendto |";
            $mail = new CMailFile($newsubject, $sendto, $from, $newmessage, array(), '', '', '', '', 0, $msgishtml, $errorsto);

            $resmail = $mail->sendfile();
            if ($resmail) {
// Mail successful
                dol_syslog("OK for Internal Notification " . $action . " to " . $sendto, LOG_DEBUG);
            } else {
// Mail unsuccessful
                dol_syslog("KO for Internal Notification " . $action . " to " . $sendto, LOG_DEBUG);
                $return_mail_errors++;
            }
        }
        if ($return_mail_errors > 0) {
            return -1;
        } else {
            return 1;
        }
    } else {
// No need to send a mail as no notification exist for that action
        dol_syslog("OK for Internal Notification " . $action . ", NO need to SEND a notification as it doesn't exist for that action", LOG_DEBUG);
        return 1;
    }
}

/**
 *  Return list of contacts emails or mobile existing for third party
 *
 * @param int $id id du tiers
 * @param string $mode 'email' or 'mobile'
 * @param int $hidedisabled 1=Hide contact if disabled
 * @return array                    Array of contacts emails or mobile array(id=>'Name <email>')
 */
function thirparty_contact_property_array($id, $mode = 'email', $hidedisabled = 0)
{
    global $langs, $conf, $db; //, $user;

    $contact_property = array();


    $sql = "SELECT rowid, email, phone_mobile";
    $sql .= " FROM " . MAIN_DB_PREFIX . "socpeople";
    $sql .= " WHERE fk_soc = '" . $id . "'";

    $resql = $db->query($sql);
    if ($resql) {
        $nump = $db->num_rows($resql);
        if ($nump) {
            $i = 0;
            while ($i < $nump) {
                $obj = $db->fetch_object($resql);
                if ($mode == 'email')
                    $property = $obj->email;
                else if ($mode == 'mobile')
                    $property = $obj->phone_mobile;
                else
                    $property = $obj->$mode;

                // Show all contact. If hidedisabled is 1, showonly contacts with status = 1
                if ($obj->statut == 1 || empty($hidedisabled)) {
                    if (empty($property)) {
                        if ($mode == 'email')
                            $property = $langs->trans("NoEMail");
                        else if ($mode == 'mobile')
                            $property = $langs->trans("NoMobilePhone");
                    }

                    $contact_property[$obj->rowid] = trim($property);
                }
                $i++;
            }
        }
    } else {
        dol_print_error($db);
    }
    return $contact_property;
}

/**
 * br2nl : br2nl opposite
 *
 * @param mixed $string
 * @return string
 */
function br2nl($str)
{
    return preg_replace('#<br\s*/?>#i', "\n", $str);
}
