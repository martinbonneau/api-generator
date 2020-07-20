<?php
// show error reporting
error_reporting(E_ALL);
 
// set your default time-zone
date_default_timezone_set('Europe/Paris');
 
///////////////////////////////////// variables used for jwt //////////////////////////////////////

// You'll just fin here a base of variables
// complete reference can be found here : https://www.iana.org/assignments/jwt/jwt.xhtml
//                             and here : https://tools.ietf.org/html/rfc7519


/**
 * By default we use a scret key for encryption
 * 
 * You can use private/public key too, refer to
 * JWT documentation or source code (in /api/libs)
 * for change the method encryption.
 * 
 * Some help :  you have to adjust /api/login.php (generation of the token),
 *              maybe your user.update() method
 *              and the /api/validate_token.php file (decryption of the token)
 */
$key = "private encryption key here";


/**
 * [optionnal]
 * 
 *  The "iss" (issuer) claim identifies the principal that issued the
 *  JWT.  The processing of this claim is generally application specific.
 *  The "iss" value is a case-sensitive string containing a StringOrURI
 *  value.  Use of this claim is OPTIONAL.
 */
$iss = "https://$domain_name$";


/**
 * [optionnal]
 * 
 * The "sub" (subject) claim identifies the principal that is the
 * subject of the JWT.  The claims in a JWT are normally statements
 * about the subject.  The subject value MUST either be scoped to be
 * locally unique in the context of the issuer or be globally unique.
 * The processing of this claim is generally application specific.  The
 * "sub" value is a case-sensitive string containing a StringOrURI
 * value.  Use of this claim is OPTIONAL.
 */
$aud = "https://$domain_name$";

?>