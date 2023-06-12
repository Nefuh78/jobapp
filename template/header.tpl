<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bewerbung Testaufgabe - Statistiken</title>
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>        
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.1.1/chart.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.1.1/helpers.esm.min.js"></script>
        <script src="./template/js/global.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">        
        <link href="./template/fontawesome/css/fontawesome.css" rel="stylesheet">
        <link href="./template/fontawesome/css/brands.css" rel="stylesheet">
        <link href="./template/fontawesome/css/solid.css" rel="stylesheet">
        <link href="./template/css/main.css" rel="stylesheet">
    </head>
    <body>
        <header>
            <div class="d-flex justify-content-between align-items-center p-2">
                <div id="sitetitle" class="fw-bolder w-25">Bewerbung Testaufgabe Statistiken</div>
                <div id="refresh" class="w-50 text-center fw-bolder d-flex align-items-center justify-content-center">
                    Zeitraum von <input class="form-control w-25 ms-1 me-1" type="date" min="{$start_date}" max="{$end_date}" value="{$current_start_date}" id="start_date" onchange="load_data();" /> 
                    bis <input class="form-control w-25 ms-1 me-1" type="date" id="end_date" min="{$start_date}" max="{$end_date}" value="{$current_end_date}" onchange="load_data();" />
                </div>
                <div class="text-end fw-bolder w-25"><span id="headerdate"></span> - <span id="headertime"></span></div>
            </div>
        </header>