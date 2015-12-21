<!DOCTYPE html>
<html lang="ca">

<head>
    <title>Consultes</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<header>
    <h3>PBL de Oriol Mejias, Marta Grau i Marc Llobera</h3>
    <a href="principal.php">
        <input type='submit' value='Torna enrere' class='info btn-primary'><br>
    </a>
</header>

<div id="content">

    <?php
    $GLOBALS['aprosus']= 1;
    if (isset($_POST['nom_Alum_Assig'])) {
        nom_Alum_Assig();
    }
    if (isset($_POST['mitjaperCurs'])) {
        mitjaperCurs();
    }

    if (isset($_POST['mitjaperAssignatura'])) {
        mitjaperAssignatura();
    }

    if (isset($_POST['numAlumnesAssignatura'])) {
        numAlumnesAssignatura();
    }
    if (isset($_POST['numAlumnesCurs'])) {
        numAlumnesCurs();
    }
    if (isset($_POST['Assig_AproSusp'])) {
        AssigAproSusp();
    }


    /**
     * Funció dibuixaEixos
     * Li pasem  per els parametres el array assignatures i el array de notes
     * Creem el grafic, el guardem i el mostrem
     */
    function dibuixaEixos($array_assignatura, $array_notes){
        $colorAprobats = '#00cc00'; //fillColorA
        $colorSuspesos = '#ff0000'; //fillColorS

        $im = new Imagick();
        $im->newImage(1400, 500, 'White');
        $draw = new ImagickDraw();
        $draw->translate(25, 500 - 25);
        //$draw->setFillColor('none');
        $draw->setStrokeColor('Black');
        $draw->setStrokeWidth(1);
        $draw->setFont("fonts/Aaargh.ttf");
        $draw->setFontSize(12);
        /**
         * Dibuixa els eixos x i y
         *
         */
        $draw->line(0, 0, 50*count($array_assignatura)+15, 0); //eix x _
        $draw->line(0, 0, 0, -45 * 10); //eix y |
        /**
         * Dibuixa les linies del eix y
         *
         */
        $n=0;
        for ($i = 0; $i <= 45 * 10; $i++){
            if ($i % 45 == 0) {
                $draw->line(0, -$i, -5, -$i);
                $draw->annotation(-15, -$i, $n);$n++;
            }
        }

        $draw->setFontSize(13);
        $i = 15;$n=0;
        foreach($array_notes as $nota) {
            if($GLOBALS['aprosus']){
                if ($nota<5) {
                    $draw->setFillColor($colorSuspesos);
                }
                else{
                    $draw->setFillColor($colorAprobats);
                }}
            else{
                $draw->setFillColor('#0000ff');
            }
            $draw->rectangle($i, 0, $i + 45, -$nota * 45);
            /* Escriu el text */

            $draw->annotation($i, 15, $array_assignatura[$n]);



            $n++;
            $i = $i+45+15;
        }


        $im->drawImage( $draw );
        $im->setImageFormat ("png");
        //file_put_contents ("draw_polyline.png", $imagick);
        $im->writeImage('draw_grafic.jpg');
        //echo $num_assig." i ".$max_notes;
        echo "<img src='draw_grafic.jpg'/>";

    }
    /**
     * Funció AssigAproSusp
     * Li pasem  per el metode post el nom_assignatura
     * Fem la consulta y ens retorna en dos arrays array assignatura i el array amb el nombre d'aprobat y de suspesos
     */
    function AssigAproSusp() {
        $GLOBALS['aprosus']=0;

        $mysqli = mysqli_connect("localhost","root","root","escola");
        $nom_assignatura = $_POST['nom_assignatura'];
        echo "<h3>Alumnes aprovats i suspesos de l'assignatura: ".$nom_assignatura."</h3> ";
        //echo $nom_alumne;

        $array_Frase = array("aprobats","suspesos");//Array per passar al imagemagic sera el nom que surt abaix
        $array_assignatura = array();
        $array_apro_sus = array();
        $sentencia = $mysqli -> prepare("SELECT count(nota) FROM cursen where codi_assignatura= ? and nota>=5");
        $sentencia -> bind_param ( 's', $nom_assignatura );
        $sentencia->bind_result($num_apro);
        $sentencia -> execute ();
        $usuari="root";
        $contrasenya="root";
        $gbd = new PDO ( 'mysql:host=localhost;dbname=escola' , $usuari ,$contrasenya);
        $sentencia1 = $gbd -> prepare ( "SELECT count(nota) FROM cursen where codi_assignatura= :nom_assignatura and nota<5" );
        $sentencia1 -> bindParam ( ':nom_assignatura' , $nom_assignatura );
        $sentencia1->bindColumn(1, $num_susp);
        $sentencia1 -> execute ();
        if ($sentencia->fetch())
        {
            array_push($array_assignatura, $nom_assignatura);
            //echo $nom_assignatura;
            array_push($array_apro_sus, $num_apro);
            //echo $num_apro;
        }
        if ($sentencia1 -> fetch ())
        {

            array_push($array_apro_sus, $num_susp);
            //echo $num_susp;
        }dibuixaEixos($array_Frase, $array_apro_sus);
        $GLOBALS['aprosus']=1;
    }

    /**
     * Funció nom_Alum_Assig
     * Li pasem  per el metode post el nom_assignatura
     * Fem la consulta y ens retorna en dos arrays array assignatura i el array amb les notes
     */

    function nom_Alum_Assig() {

        $mysqli = mysqli_connect("localhost","root","root","escola");
        $nom_alumne = $_POST['nom_alumne_assignatura'];
        //echo $nom_alumne;
        echo "<h3>Notes per assignatura del alumne: ".$nom_alumne."</h3> ";

        $array_assignatura = array();
        $array_notes = array();
        $sentencia = $mysqli -> prepare("Select c.codi_assignatura,c.nota from cursen c join alumne a on c.codi_alumne=a.codi_alumne where c.codi_alumne=(select codi_alumne from alumne where nom_alumne=?)");
        $sentencia -> bind_param ( 's', $nom_alumne );
        $sentencia->bind_result($codi_assignatura,$nota);
        $sentencia -> execute ();
        while ($sentencia->fetch())
        {
            array_push($array_assignatura, $codi_assignatura);//Assignatures que li pasarem al imgmagiik
            //echo $codi_assignatura;
            array_push($array_notes, $nota);//Notes que li pasarem al imgmagik
            //echo $nota;

        }dibuixaEixos($array_assignatura, $array_notes);
    }

    /**
     * Funció mitjaperCurs
     *
     * Fem la consulta y ens retorna en dos arrays array cursos i el array amb les notes mitjes
     */
    function mitjaperCurs() {
        echo "<h3>Mitja de notes per curs</h3> ";
        $mysqli = mysqli_connect("localhost","root","root","escola");
        $resultado = mysqli_query($mysqli,"SELECT codi_curs FROM curs");//les sentencies no es posen mai a la iteració
        $sentencia = $mysqli -> prepare("SELECT avg(c.NOTA) FROM cursen c join assignatura a on c.codi_assignatura=a.codi_assignatura WHERE a.codi_curs = ?" );//ha d'estar fora
        $filas=array();
        $error=array();
        //$json_response=array();
        $array_notes = array();
        $array_codi_curs = array();

        while($fila=mysqli_fetch_assoc($resultado)){
            $filas['codi_curs'] = (int)$fila['codi_curs'];
            $codi_curs=$filas['codi_curs'];
            array_push($array_codi_curs, $codi_curs);//Array dels cursos que agafarem per el imgmagiic
            //echo $codi_curs." ";
            $sentencia->bind_param('s',$codi_curs);
            $sentencia->execute();
            $sentencia->bind_result($nota);
            if ($sentencia->fetch())
            {
                if($nota==null){
                    $nota=0;
                }
                array_push($array_notes, $nota);//Array de les mitjes que agafarem per el imgmagiic
                //echo $nota." ";
                //$sentencia->close();
            }
        }dibuixaEixos($array_codi_curs, $array_notes);

    }
    /**
     * Funció mitjaperAssignatura
     *
     * Fem la consulta y ens retorna en dos arrays array assignatures i el array amb les notes mitjes
     */
    function mitjaperAssignatura() {
        echo "<h3>Mitja de notes per assignatura</h3> ";

        $mysqli = mysqli_connect("localhost","root","root","escola");
        $resultado = mysqli_query($mysqli,"SELECT codi_assignatura from assignatura");//les sentencies no es posen mai a la iteració
        $sentencia = $mysqli -> prepare("SELECT avg(c.NOTA) FROM cursen c join assignatura a on c.codi_assignatura=a.codi_assignatura WHERE a.codi_assignatura = ?" );//ha d'estar fora
        $filas=array();
        $error=array();
        //$json_response=array();
        $array_notes = array();
        $array_codi_assignatura = array();

        while($fila=mysqli_fetch_assoc($resultado)){
            $filas['codi_assignatura'] = $fila['codi_assignatura'];
            $codi_assignatura=$filas['codi_assignatura'];
            array_push($array_codi_assignatura, $codi_assignatura);//Array de les assignatures que agafarem per el imgmagiic
            //echo $codi_assignatura." ";
            $sentencia->bind_param('s',$codi_assignatura);
            $sentencia->execute();
            $sentencia->bind_result($nota);
            if ($sentencia->fetch())
            {
                if($nota==null){
                    $nota=0;
                }
                array_push($array_notes, $nota);//Array de les mitjes que agafarem per el imgmagiic
                //echo $nota." ";
                //$sentencia->close();
            }
        } dibuixaEixos($array_codi_assignatura, $array_notes);
    }

    /**
     * Funció numAlumnesAssignatura
     *
     * Fem la consulta y ens retorna en dos arrays array assignatures i el array amb el numeros d'alumne per assignatura
     */
    function numAlumnesAssignatura() {
        echo "<h3>Número d'alumnes per assignatura</h3> ";
        $GLOBALS['aprosus']=0;

        $mysqli = mysqli_connect("localhost","root","root","escola");
        $resultado = mysqli_query($mysqli,"SELECT codi_assignatura from assignatura");//les sentencies no es posen mai a la iteració
        $sentencia = $mysqli -> prepare("select count(codi_alumne) from cursen where codi_assignatura=?" );//ha d'estar fora
        $filas=array();
        $error=array();
        //$json_response=array();
        $array_num_alumne = array();
        $array_codi_assignatura = array();


        while($fila=mysqli_fetch_assoc($resultado)){
            $filas['codi_assignatura'] = $fila['codi_assignatura'];
            $codi_assignatura=$filas['codi_assignatura'];
            array_push($array_codi_assignatura, $codi_assignatura);//Array de les assignatures que agafarem per el imgmagiic
            //echo $codi_assignatura." ";
            $sentencia->bind_param('s',$codi_assignatura);
            $sentencia->execute();
            $sentencia->bind_result($num_alumne);
            if ($sentencia->fetch()){

                array_push($array_num_alumne, $num_alumne);//Array del numero d'alumnes que agafarem per el imgmagiic
                //echo $num_alumne." ";
                //$sentencia->close();
            }
        }dibuixaEixos($array_codi_assignatura, $array_num_alumne);
        $GLOBALS['aprosus']=1;
    }

    /**
     * Funció numAlumnesCurs
     *
     * Fem la consulta y ens retorna en dos arrays array cursos i el array amb nombre d'alumne per curs
     */
    function numAlumnesCurs(){
        echo "<h3>Número d'alumnes per curs</h3> ";
        $GLOBALS['aprosus']= 0;

        $mysqli = mysqli_connect("localhost","root","root","escola");
        $resultado = mysqli_query($mysqli,"SELECT codi_curs FROM curs");//les sentencies no es posen mai a la iteració
        $sentencia = $mysqli -> prepare("select count(c.codi_alumne) from cursen c join assignatura a on a.codi_assignatura=c.codi_assignatura where a.codi_curs=?" );//ha d'estar fora
        $filas=array();
        $error=array();
        //$json_response=array();
        $array_num_alumne = array();
        $array_codi_curs = array();

        while($fila=mysqli_fetch_assoc($resultado)){
            $filas['codi_curs'] = $fila['codi_curs'];
            $codi_curs=$filas['codi_curs'];
            array_push($array_codi_curs, $codi_curs);//Array dels cursos que agafarem per el imgmagiic
            //echo $codi_curs." ";
            $sentencia->bind_param('s',$codi_curs);
            $sentencia->execute();
            $sentencia->bind_result($num_alumne);
            if ($sentencia->fetch())
            {

                array_push($array_num_alumne, $num_alumne);//Array del numero d'alumnes que agafarem per el imgmagiic
                //echo $num_alumne." ";
                //$sentencia->close();
            }
        }  dibuixaEixos($array_codi_curs, $array_num_alumne);
        $GLOBALS['aprosus']=1;
    }


    /*
       notesAlumnes();
        function notasAlumnes() {
            $mysqli = mysqli_connect("localhost","root","root","escola");
            $resultado = mysqli_query($mysqli,"SELECT a.nom_alumne, a.codi_alumne , c.nota FROM alumne a join cursen c on a.codi_alumne=c.codi_alumne");
            $filas=array();
            $error=array();
            $json_response=array();
            $contar = mysqli_num_rows($resultado);

                while($fila=mysqli_fetch_assoc($resultado)){
                    $filas['nom_alumne'] = $fila['nom_alumne'];
                    $filas['codi_alumne'] = $fila['codi_alumne'];
                    $filas['nota'] = $fila['nota'];


                }

           echo json_encode($json_response);
            mysqli_close($mysqli);
        }
    */

    //}
    ?>
</div>

</body>
</html>