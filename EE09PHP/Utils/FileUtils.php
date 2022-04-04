<?php

$vv=new \EE09\ApiResponse();
the()->apiResponse=$vv;
$vv->usePayload();

$vv->addMessage("mise à jour d'une instances");

// test des variables request
//TODO captcha ou un truc pour éviter les attaques DOS


// l'instance à mettre à jour
$name=$vv->testAndGetRequest("name");
$inst=null;
if($vv->success){
    $inst=new \Models\RecapInstance($name);
    if(!$inst->isValid){
        //$vv->addError("instance $name invalide");
    }
    $vv->addToBody("inst",$inst);

}

// l'instance master
$master=new \Models\RecapInstance("master");
if($vv->success){
    if(!$master->isValid){
        $vv->addError("Master invalide");
    }else{
        $vv->addToBody("master",$master);
    }
}

if($vv->success){
    $vv->addMessage("passage de la version $inst->version à la version $master->version");

    //répertoires à remplacer
    $replaceDirs=[
        "admin",
        "js",
        "fonts",
        "css",
        "img",
        "server/vendor",
        "server/project",
        "server/php-classes",
        "server/configs",
    ];

    //fichiers à remplacer
    $replaceFiles=[
        "confs/default-texts.json",
        ".htaccess",
        "favicon.ico",
        "index.html",
        "service-worker.js",
        "manifest.json",
        "version.txt",
    ];

    $vv->addMessage("Répertoires...");
    foreach ($replaceDirs as $item){
        $path="../".$inst->name."/".$item;
        $masterPath="../master/".$item;
        //effaçage de répertoires
        if(is_dir($path)){
            $success=utils()->files->removeDir($path);
            $vv->addMessage("Effacer $path",$success);
            if($success===false){
                $vv->addError("Effacer dir $path failed");
            }
        }else{
            $vv->addMessage("instance $path introuvable");
        }


        //copie de répertoires
        if(is_dir($masterPath)){
            $vv->addMessage("Remplacer par $masterPath");
            $success=utils()->files->copyDir($masterPath,$path);
            if($success===false){
                $vv->addError("Copie de dir $path failed");
            }
        }else{
            $vv->addError("master dir $masterPath invalide");
        }

    }


    $vv->addMessage("Fichiers.............");
    foreach ($replaceFiles as $item){
        $path="../".$inst->name."/".$item;
        $masterPath="../master/".$item;
        //effaçage de fichiers
        if(is_file($path)){
            $success=unlink($path);
            $vv->addMessage("Effacer $path",$success);
            if($success===false){
                $vv->addError("Effacer file $path failed");
            }
        }else{
            $vv->addMessage("instance $path introuvable");
        }

        //copie de fichiers
        if(is_file($masterPath)){
            $vv->addMessage("Remplacer par $masterPath");
            $success=copy($masterPath,$path);
            if($success===false){
                $vv->addError("Copie de file $path failed");
            }
        }else{
            $vv->addError("master file $masterPath invalide");
        }

    }



    //TODO gérer precache-manifest-xxxxx.js


}


$view->inside("json", $vv);
