<?php 
//Archivo de validaciones

//validar que un campo no este vacio
function validar_campo($campo){
    if (trim($campo) == "") {
        return false;
    } else {
        return true;
    }
}
//validar que sea un nombre valido con preg match
function validarnombre($nombre){
    if (ctype_alpha($nombre)){
        return true;
    }else {
        return false;
    }

}   

//validar email
function validar_email($email){
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    } else {
        return false;
    }
}


// validar la longitud de la contraseña
function longitud_password($password){
    if (strlen($password) >= 5) {
        return true;
    } else {
        return false;
    }
}