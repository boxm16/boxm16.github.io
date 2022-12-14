<?php
session_start();
require_once 'Controller/ConfigController.php';
if ($_SESSION["authorized"] == "true") {
    //you can go on
} else {
    header("Location: adminGate.php?authorizationResult=notAuthorized");
}
$configController = new ConfigController();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <a href="adminMenu.php">Go Admin</a>
        <hr>
        <h1>Configurations</h1>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <input hidden name="createTables">
            <button type="submit">Create Tables</button>
        </form>
        <?php
        if (isset($_POST["createTables"])) {
            //precedence is important, there are primary-foreign keys rstrictions
            $configController->createTables();
        }
        ?>



        <hr>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <input hidden name="insertMainMapVersion">
            <button type="submit">Insert Main Map Version</button>
        </form>
        <?php
        if (isset($_POST["insertMainMapVersion"])) {
            //precedence is important, there are primary-foreign keys rstrictions
            $configController->insertMainMapVersion();
        }
        ?>

        <hr>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <input hidden name="insertMainPerson">
            <button type="submit">Insert Main Person</button>
        </form>
        <?php
        if (isset($_POST["insertMainPerson"])) {
            //precedence is important, there are primary-foreign keys rstrictions
            $configController->insertMainPerson();
        }
        ?>

        <hr><hr><hr>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <input hidden name="deleteTables">
            <button type="submit">Delete Tables</button>
        </form>
        <?php
        if (isset($_POST["deleteTables"])) {
            //precedence is important, there are primary-foreign keys rstrictions
            $configController->deleteTables();
        }
        ?>

        <hr>
        <a href="justForMigration.php"><h3>Click To Migrate Database</h3></a>

    </body>
</html>
