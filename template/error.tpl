<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bewerbung Testaufgabe - Error</title>
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
        <main class="container-float">
            <div class="d-flex aligns-items-center justify-content-center bg-secondary fullheight">
                <div class="card w-50 mx-auto border border-danger errorcard">
                    <div class="card-header bg-danger text-white fs-4">{$error_title}</div>
                    <div class="card-body fs-5">{$error_message}</div>
                    <div class="card-footer text-body-secondary text-center"><a class="btn btn-warning" href="./index.php">Reload</a></div> 
                </div>
            </div>
        </main>
    </body>
</html>