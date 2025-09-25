<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php echo $project['name'] ?></title>
    <style>
    .container {
        margin: 30px;
        text-align: justify;
        text-justify: inter-word;
    }
    .milestone-title {
        margin-bottom: -10px;
    }
    </style>
    
    <body>
        <div class="container">
        <h2><?php echo $project['name'] ?></h2>
        <div>
            <?php
            foreach ($milestones as $milestone) {
                echo '<h3 class="milestone-title">'.$milestone['name'].'</h3>'.$milestone['exp'];
            }
            ?>
        </div>
        </div>
    </body>
