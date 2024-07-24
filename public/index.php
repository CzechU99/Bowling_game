<?php

    use App\GuiHtml;
    use App\PrinterHtml;
    use App\ObslugaIOHtml;
    use App\StanGryManager;

    require(realpath(__DIR__ . "/../vendor/autoload.php"));

    session_start();

    $printerHtml = new PrinterHtml();
    $obslugaIOHtml = new ObslugaIOHtml();
    $stanGryManager = new StanGryManager(session_id());
    $guiHtml = new GuiHtml($obslugaIOHtml, $stanGryManager);

    $stanGryManager->usunStanGry();
    
    $printerHtml->printIndexHead();
    $printerHtml->printIndexHtml();
    
    $obslugaIOHtml->wczytajLiczbeGraczy("liczbaGraczy");

    $guiHtml->przekierujNaPlayersHtml("liczbaGraczy");