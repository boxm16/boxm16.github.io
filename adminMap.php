<?php
session_start();

require_once 'Controller/MapVersionController.php';
require_once 'Controller/PersonController.php';

if ($_SESSION["authorized"] == "true") {
    //you can go on
} else {
    header("Location: adminGate.php?authorizationResult=notAuthorized");
}
$mapVersionId = $_POST["mapVersionId"];
if ($mapVersionId == null) {
    header("Location: errorPage.php");
}
$mapVersionController = new MapVersionController();
$mapVersion = $mapVersionController->getMapVersion($mapVersionId);
$width = $mapVersion->getMapWidth();
$height = $mapVersion->getMapHeight();



$personController = new PersonController();
$personsList = $personController->getAllPersonsForMap($mapVersionId);
if (isset($_GET["personInFocusId"])) {
    $personInFocusId = $_GET["personInFocusId"];
} else if (isset($_POST["personInFocusId"])) {
    $personInFocusId = $_POST["personInFocusId"];
} else {
    $personInFocusId = 1;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title></title>
        <script src="https://code.jquery.com/jquery-2.2.1.min.js"></script>
        <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
        <style>
            svg {
                position: absolute;
                left: 0;
                top: 0;

            }
        </style>
    </head>
    <body>

        <svg style="background-color:skyblue" width="<?php echo $width ?>"  height="<?php echo $height ?>" ondblclick="redirectToAdminMenu();">
        <?php
        foreach ($personsList as $person) {
            if ($person->getParentId() == 0) {
                //no need for line, line goes only from child to parent  
            } else {
                $parentId = $person->getParentId();
                $personId = $person->getId();
                $x1 = $person->getParentPositionX() + 42;
                $y1 = $person->getParentPositionY() + 42;
                $x2 = $person->getPositionX() + 42;
                $y2 = $person->getPositionY() + 42;
                $lineId = 'line_' . $personId . '_' . $parentId;

                echo "<line id='$lineId' x1='$x1' y1='$y1' x2='$x2' y2='$y2' style='stroke:rgb(255,0,0)'></line>";
            }
        }

        foreach ($personsList as $person) {

            $id = $person->getId();
            $generation = $person->getGeneration();
            $x = $person->getPositionX();
            $y = $person->getPositionY();
            $parentPositionX = $person->getParentPositionX();
            $parentPositionY = $person->getParentPositionY();
            $firstName = $person->getFirstName();
            $secondName = $person->getSecondName();
            $firstNameX = $x;
            $firstdNameY = $y - 10;
            $secondNameX = $x;
            $secondNameY = $firstdNameY + 15;

            $parentId = $person->getParentId();
            $children = $person->getChildren();
            $name = $parentId . ':';
//name actually is code for for a person, it contains parent Id and children ids
            foreach ($children as $child) {
                $childId = $child->getId();
                $name = $name . $childId . ',';
            }

            echo "<svg id='$id' class='movingCircle' name='$name' style='cursor: default' x='$x' y='$y' >";
            echo "<g id='$id'  ondblclick='redirect(event, $id)'>";
           if ($id == 1) {
                echo "<circle   cx='42' cy='42' r='40' stroke='red' stroke-width='4' fill='yellow' />";
            } else if ($personInFocusId == $id) {
                echo "<circle   cx='42' cy='42' r='40' stroke='green' stroke-width='4' fill='lime' />";
            } else {
                echo "<circle   cx='42' cy='42' r='40' stroke='green' stroke-width='4' fill='yellow' />";
            }

            echo "<text x='42' y='30' text-anchor='middle' fill='black' font-size='15px' font-family='Arial' dy='.3em'>
        $firstName 
        </text>;
        <text x='42' y='50' text-anchor='middle' fill='black' font-size='13px' font-family='Arial' dy='.3em'>
        $secondName      
        </text>
        <text x='42' y='70' text-anchor='middle' fill='black' font-size='15px' font-family='Arial' dy='.3em'>
        $generation 
        </text>;";
            echo "</g>";
            echo "</svg>";
        }
        ?>
        </svg>
        <form action="versionMenu.php" method="POST">
            <input  name="mapVersionId" hidden value="<?php echo $mapVersionId; ?>">
            <input id="mapPositioningChanged" name="mapPositioningChanged" hidden value="false">
            <input id="allPositions" name="allPositions" hidden value="">

        </form>

        <script>
            window.addEventListener("load", centerPersonInFocus());
            function centerPersonInFocus() {
                document.getElementById('<?php echo $personInFocusId ?>').scrollIntoView({
                    behavior: 'auto',
                    block: 'center',
                    inline: 'center'
                });
            }
//---------------------THIS PART IS FOR MOVING CIRCLES AND LINES -----------------------------
            var allCircles = document.querySelectorAll(".movingCircle");
            allCircles.forEach(element => dragMovingCirlce(element));

            var mapPositioningChanged = false;

            function dragMovingCirlce(elmnt) {


                var mousePosStartX = 0, mousePosStartY = 0, mousePosEndX = 0, mousePosEndY = 0;
                var movingCirclePosStartX = 0, movingCirclePosStartY = 0, movingCirclePosEndX = 0, movingCirclePosEndY = 0;
                var diffPosX = 0, diffPosY = 0;
                elmnt.onmousedown = dragMouseDown;
                function dragMouseDown(e) {
                    e = e || window.event;
                    e.preventDefault();
                    // get the mouse cursor position at startup:
                    mousePosStartX = e.clientX;
                    mousePosStartY = e.clientY;
                    movingCirclePosStartX = elmnt.getAttribute("x");
                    movingCirclePosStartY = elmnt.getAttribute("y");
                    diffPosX = movingCirclePosStartX - mousePosStartX;
                    diffPosY = movingCirclePosStartY - mousePosStartY;

                    document.onmouseup = closeDragElement;
                    // call a function whenever the cursor moves:
                    document.onmousemove = elementDrag;
                }

                function elementDrag(e) {
                    e = e || window.event;
                    e.preventDefault();
                    // calculate the new cursor position:
                    mousePosEndX = e.clientX;
                    mousePosEndY = e.clientY;

                    movingCirclePosEndX = mousePosEndX + diffPosX;
                    movingCirclePosEndY = mousePosEndY + diffPosY;
                    // set the element's new position:
                    elmnt.setAttribute("x", movingCirclePosEndX);
                    elmnt.setAttribute("y", movingCirclePosEndY);

                    let name = elmnt.getAttribute("name");
                    let parentChildren = name.split(':');
                    let parentId = parentChildren[0];
                    let children = parentChildren[1].split(',');

                    children.forEach((childId) => {
                        line_y_1 = movingCirclePosEndY + 42;
                        line_x_1 = movingCirclePosEndX + 42;
                        let meToChildLineId = '#line_' + childId + '_' + elmnt.id;
                        $(meToChildLineId).attr({x1: line_x_1, y1: line_y_1})
                    })

                    line_y_2 = movingCirclePosEndY + 42;
                    line_x_2 = movingCirclePosEndX + 42;
                    let meToParentId = '#line_' + elmnt.id + '_' + parentId;
                    $(meToParentId).attr({x2: line_x_2, y2: line_y_2})

                }
                function closeDragElement() {
                    /* stop moving when mouse button is released:*/
                    document.onmouseup = null;
                    document.onmousemove = null;

                    if (mapPositioningChanged == true) {
//do nothing
                    } else {
                        mapPositioningChanged = true;
                        document.getElementById("mapPositioningChanged").value = true;
                    }
                }
            }
//---------------------- END OF MOVING CIRCLES AND LINES ----------------------------

//------------------------REDIRECTION START ---------------------------------------
            function redirectToAdminMenu() {
                var form = document.querySelector("form");
                if (mapPositioningChanged == true) {
                    document.getElementById("allPositions").value = getAllPositions();
                }
                form.submit();

            }
            function redirect(event, personId) {

                document.location.href = "personPage.php?mapVersionId=<?php echo $mapVersionId ?> &personId=" + personId;
                event.stopPropagation();
            }
//------------------------REDIRECTING END ------------------------------

//----------------------------
            function getAllPositions() {
                let allCircles = document.querySelectorAll(".movingCircle");
                let allPostitions = "";
                for (let i = 0; i < allCircles.length; i++) {
                    let circle = allCircles[i];
                    let id = circle.id;
                    let x = circle.getAttribute("x");
                    let y = circle.getAttribute("y");
                    if (i == 0) {
                        allPostitions = allPostitions + id + "," + x + "," + y;
                    } else {
                        allPostitions = allPostitions + ":" + id + "," + x + "," + y;
                    }
                }
                return allPostitions;
            }
        </script>
    </body>
</html>



