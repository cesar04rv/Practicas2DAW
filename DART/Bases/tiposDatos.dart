void main() {

  //Numeros
  int empleados = 10;
  double pi = 3.141592;
  double numero = 1.0;
  print('$empleados - $pi - $numero');

  //String - Cadenas de caracteres

  String nombre = 'César';
  print(nombre);
  print(nombre[0]);
  print(nombre[nombre.length -1]);
}

// Booleanos 
void main() {
 bool activado = true;
 
  activado = !activado;
  if (activado == true){
    print ("El motor esta funcionando");
    
  }else{
    print("El motor está apagado");
  }
}


// Listas
void main () {
  List numeros = [1,2,3,4,5];
  print(numeros);
  numeros.add("Hola mundo");
  print(numeros);

  List<int> soloNumeros = [1,2,3,4,5];
  print(soloNumeros);
  soloNumeros.add(6);
  print(soloNumeros);

  //Tanaño fijo
  List masNumeros = List(10);
  print(masNumeros);
  // masNumeros.add(1); // No se puede agregar elementos a una lista de tamaño fijo

}


void main(){
  //Mapas
String nombre = 'César';
  Map<String, dynamic> persona = {
    'nombre' : 'César',
    'edad' : 21,
    'profesion' : 'Desarrollador'
  }
  print(persona)['nombre'];
  print(persona[nombre]);

Map<int, String> numeros = {
  1 : 'Uno',
  2 : 'Dos',
  3 : 'Tres'
}

personas.addAll({4 : 'Cuatro', 5 : 'Cinco'});
print(numeros);
}


// Funciones

void main(){

  String mensaje = saludar(texto: 'Hola', nombre: 'César');
  print(mensaje);
}

String saludar({ String texto, String nombre }){

  //print('Hola');
  return '$texto $nombre';
 }

 String saludar2({ String texto = 'Hola', String nombre = 'Mundo' }) => '$texto $nombre';


//Clases
void main(){
  final wolverine = new Heroe("Logan", "Regeneración");
  print(wolverine);
}


 class Heroe{
  String nombre;
  String poder;

 }

 Heroe(String nombre, String poder){
  this.nombre = nombre;
  this.poder = poder;
 }

 String to String(){
  return '${this.nombre} - ${this.poder}';
 }


 //Manera corta de escribir las propiedades de las clases

  void main(){
  }
    class Heroe{
      String nombre;
      String poder;

      Heroe({this.nombre, this.poder});

      String toString()=>'nombre: $nombre - poder: $poder';
      }
  


  
import 'dart:convert';

void main() {

  final rawJSON = '{"nombre": "Tony Stark", "poder": "Tecnología"}';
  
  Map<String, dynamic> parsedJson = json.decode(rawJSON);

  final wolverine = Heroe.fromJson(parsedJson);

  print(wolverine.nombre);
  print(wolverine.poder);
}

class Heroe {

  String nombre;
  String poder;

  // Constructor normal
  Heroe({
    required this.nombre,
    required this.poder,
  });

  // Constructor desde JSON
  Heroe.fromJson(Map<String, dynamic> parsedJson)
      : nombre = parsedJson['nombre'] ?? 'Sin nombre',
        poder = parsedJson['poder'] ?? 'Sin poder';
}


//Getters y setters


void main() {
  final cuadrado = Cuadrado();

  cuadrado.lado = 5;

  print(cuadrado);
  print('area: ${cuadrado.area}');
}

class Cuadrado {
  double _lado = 0.0;

  set lado(double valor) {
    if (valor <= 0) {
      throw ("El lado no puede ser menor o igual a 0");
    }
    _lado = valor;
  }

  double get area => _lado * _lado;

  String toString() => 'Lado: $_lado';
}


//Clases abstractas

//No pueden ser instanciadas de una clase abstracta
//Es un modelo a seguir

void main(){
  
  //final perro = new Animal();
}


abstract class Animal{
  int patas = 0;
  void emitirSonido();
 
}


class Perro implements Animal{
  
  
  int patas=0;
  int colas=0;
  
  void emitirSonido(){
    print("Guau");
  }

}

class Gato implements Animal{
  int patas = 0;
  void emitirSonido(){
    print("miau");
  }
}



// Extends
void main() {


  final Superman = new Heroe();
   Superman.nombre="Clark Kent";

  final Batman = new Villano();
  Batman.nombre="Paco";
}

abstract class Personaje {
  
  String nombre ="";
  String poder="";
 
}

class Heroe extends Personaje {
  
  int valentia = 0;
}

class Villano extends Personaje {

  int maldad = 0;
}


//Mixes

void main(){
  final pato = new Pato();
  pato.volar();
  
  final pezVolador = new PezVolador();
  pezVolador.volar();
}
abstract class Animal{
  
}

abstract class Mamifero extends Animal{
  
}


abstract class Ave extends Animal{
  
}

abstract class Pez extends Animal{
  
}


mixin Volador{
  void volar()=> print("Estoy volando");
}
mixin Caminante{
  void cmainar()=> print("Estoy caminando");
}
mixin Nadador{
  void nadar()=> print("Estoy nadando");
}

class Delfin extends Mamifero with Nadador{
  
}

class Murcielago extends Mamifero with Caminante, Volador{
  
}

class Gato extends Mamifero with Caminante{
  
}

class Paloma extends Ave with Caminante, Volador{
  
}

class Pato extends Ave with Caminante, Volador, Nadador{
  
}

class Tiburon extends Pez with Nadador{
  
}

class PezVolador extends Pez with Nadador, Volador{}


//Futures

void main(){
  print ("A punto de pedir datos");
  httpGet("https://api.nasa.com/aliens").then( 
   (data){
   print(data); 
  } );
  
  print("Ultima linea");
  
}

Future<String> httpGet(String url){
  return Future.delayed(new Duration(seconds: 4), (){
    return "Hola mundo";
  });
}

//Async await


void main() async{
  print ("A punto de pedir datos");
  String data = await  httpGet("https://api.nasa.com/aliens").then( 
   (data){
   print(data); 
  } );
  
  print("Ultima linea");
  
}

Future<String> httpGet(String url){
  return Future.delayed(new Duration(seconds: 4), (){
    return "Hola mundo";
  });
}

 
