<?php
require 'vendor/autoload.php';

$source = "test3.docx";
$phpWord = \PhpOffice\PhpWord\IOFactory::load($source);




$sections = $phpWord->getSections();

$section = $sections[0];

$elements = $section->getElements();


function removeFormat($text)
{
    $urlEncodedWhiteSpaceChars   = '%81,%7F,%C5%8D,%8D,%8F,%C2%90,%C2,%90,%9D,%C2%A0,%A0,%C2%AD,%AD,%08,%09,%0A,%0D';
    $temp = explode(',', $urlEncodedWhiteSpaceChars); 
    $text  = urlencode($text);
        foreach($temp as $v){
            $text  =  str_replace($v, ' ', $text);     
        }
        $text = urldecode($text);
        return $text;
}


$import = "";


foreach($elements as $value)
{
    if ( get_class( $value ) == 'PhpOffice\PhpWord\Element\TextRun' ) {


        foreach($value->getElements() as $v)
        {
            if(get_class( $v ) == 'PhpOffice\PhpWord\Element\Text')
            {
                $import .= removeFormat($v->getText());
            }

            if(get_class( $v ) == 'PhpOffice\PhpWord\Element\Image')
            {
                
                $img = 'img/img_'.uniqid().time().'.png';
                file_put_contents($img, base64_decode($v->getImageStringData(true)));
                $import .= " <img src='".$img."' /> ";
            }

        }

        
       

    }
    else if ( get_class( $value ) === 'PhpOffice\PhpWord\Element\TextBreak' ) {
        $import .= '';
    }

    else if ( get_class( $value ) === 'PhpOffice\PhpWord\Element\PageBreak' ) {
        $import .= '';  
    } 
   
}

echo $import;


echo "<br/><br/><br/><hr/><br/><br/><br/>";

$questions = array();
$question = array();


$questions_import = explode("{QUESTION BEGINS}{QUESTION TEXT}",$import);

//var_dump($questions_import[0]);


for($i=1; $i<sizeof($questions_import); $i++)
{
    $options = array();
    //var_dump($questions_import[$i]);
    //var_dump(explode("{OPTION 1}",$questions_import[$i]));

    $question_text = explode("{OPTION 1}",$questions_import[$i])[0];

    $rest = explode("{OPTION 1}",$questions_import[$i])[1];

    //print($question_text);

    
    for($j=2; $j<8; $j++)
    {
        
        if($j != 7)
        {
            
            if(strlen(trim(explode("{OPTION ".$j."}",$rest)[0]))>0 && trim(explode("{OPTION ".$j."}",$rest)[0]) != "")
            {
                $options[] = trim(explode("{OPTION ".$j."}",$rest)[0]);
            }
            $rest = explode("{OPTION ".$j."}",$rest)[1];
        }
        else
        {
           

            if(strlen(trim(explode("{RIGHT ANSWER}", explode("{OPTION ".$j."}",$rest)[0])[0]))>0 && trim(explode("{RIGHT ANSWER}", explode("{OPTION ".$j."}",$rest)[0])[0]) != "")
            {
                $options[] = trim(explode("{RIGHT ANSWER}", explode("{OPTION ".$j."}",$rest)[0])[0]);
            }
            $rest = explode("{RIGHT ANSWER}", explode("{OPTION ".$j."}",$rest)[0])[1];
        }

        
    }

    $right_answer = explode("{EXPLANATION}",$rest)[0];
    $rest = explode("{EXPLANATION}",$rest)[1];
    
    $explanation = explode("{DIFFICULTY}",$rest)[0];
    $rest = explode("{DIFFICULTY}",$rest)[1];

    $difficulty = explode("{QUESTION ENDS}",$rest)[0];
    
    $question['title'] = trim($question_text);
    $question['options'] = $options;
    $question['no_of_options'] = sizeof($options);
    $question['right_answer'] = trim($right_answer);
    $question['explanation'] = trim($explanation);
    $question['difficulty'] = trim($difficulty);

    $questions[] = $question;
    
    

    
}



//var_dump($questions);
echo "<pre>";
echo json_encode($questions , JSON_PRETTY_PRINT);
echo "</pre>";



?>