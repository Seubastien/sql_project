<?php
function connection($servname, $usname, $pass)
{
    $db = new PDO("mysql:host=$servname;dbname=project_sql", $usname, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $db;
}
function delete($db, $id)
{
    $requeteSQLDelete = $db->prepare("DELETE FROM user WHERE Id = $id");
    $requeteSQLDelete->execute();
}
function add($db, $name, $firstName, $mail, $postal)
{
    $requetSQLAdd = $db->prepare("INSERT INTO  user (Nom, Prenom, Mail, Code_Postal) VALUES(:Nom, :Prenom, :Mail, :Code_Postal)");
    $requetSQLAdd->bindParam(':Nom', $name);
    $requetSQLAdd->bindParam(':Prenom', $firstName);
    $requetSQLAdd->bindParam(':Mail', $mail);
    $requetSQLAdd->bindParam(':Code_Postal', $postal);
    $regex = regex($name, $firstName, $mail, $postal);
    // Bien mettre 3 "="
    if ($regex === true) {
        $requetSQLAdd->execute();
        return "";
    } else {
        return $regex;
    }
}
function update($db, $id, $name, $firstName, $mail, $postal)
{
    $requeteSQLUpdate = $db->prepare("UPDATE USER SET Nom='$name', Prenom='$firstName', Mail='$mail', Code_Postal='$postal' WHERE Id = $id");
    $regex = regex($name, $firstName, $mail, $postal);
    // Bien mettre 3 "="
    if ($regex === true) {
        $requeteSQLUpdate->execute();
        return "";
    } else {
        return $regex;
    }
}
function regex($name, $firstName, $mail, $postal)
{
    $regName = "/^[a-zA-ZÀ-ÿ\s'-]{2,}$/";
    $regFirstname = "/^[a-zA-ZÀ-ÿ\s'-]{2,}/";
    $regMail = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
    $regPostal = "/^\d{5}(?:-\d{4})?$/";
    if (!preg_match($regName, $name)) {
        return "'/!\Nom invalide/!\'";
    }
    if (!preg_match($regFirstname, $firstName)) {
        return "'/!\Prénom invalide/!\'";
    }
    if (!preg_match($regMail, $mail)) {
        return "'/!\Mail invalide/!\'";
    }
    if (!preg_match($regPostal, $postal)) {
        return "'/!\Code-Postal invalide/!\'";
    }
    return true;
}
function display($db)
{
    $requeteSQL = $db->prepare("SELECT Id, Nom, Prenom, Mail, Code_Postal FROM user");
    $requeteSQL->execute();
    $tableauRequete = $requeteSQL->fetchAll();
    // echo "<pre>" , var_dump($tableauRequete), "</pre>";
    foreach ($tableauRequete as $line) {
        echo "<tr class = 'line'><form method='POST'>";
        foreach ($line as $key => $value) {
            if ($key != "Id") {
                if (isset($_POST['update'])) {
                    if ($_POST['update'] == $line['Id']) {
                        echo "<td class = 'cell'><input id ='inputModif' type='text' name='$key' value='$value'></td>";
                    } else {
                        echo "<td class = 'cell'>$value</td>";
                    }
                } else {
                    echo "<td class = 'cell'>$value</td>";
                }
            } else {
                $id = $value;
            }
        }
        echo "<td>
                <button class='button' type='submit' value='$id' name='delete'>Suppr</button>
                   </td>";
        if (!isset($_POST['update'])) {
            echo "<td>
                 <button class='button' type='submit' value='$id' name='update'>Modif</button>
              </td>";
        } else {
            if ($_POST['update'] == $line['Id']) {
                echo "<td> 
                 <button class='button' type='submit' value='$id' name='confirm'>Valider</button>
               
                </td>";
            } else {
                echo "<td> 
                <button class='button' type='submit' value='$id' name='update'>Modif</button>
              </td>";
            }
        }
        echo "</form></tr>";
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/style.css">
    <title>UserBaseData</title>
</head>


<body>
    <?php
    $servername = 'localhost'; // Nom du serveur
    $username = 'root'; // Login pour se connecter à votre base de données
    $password = 'MLum!ny1313M'; // Mot de passe pour se connecter à votre base de données
// On essaie de se connecter à la base de donnée
    ?>
    <table class="table">
        <tr id="trHeader">
            <th>Nom</th>
            <th>Prénom</th>
            <th>E-Mail</th>
            <th>Code Postal</th>
            <th>Action</th>
        </tr>
        <?php
        try {
            $db = connection($servername, $username, $password);
            $db->beginTransaction();
            if (isset($_POST["delete"])) {
                delete($db, $_POST["delete"]);
                header('refresh:0');
            }
            if (isset($_POST["add"])) {

                $error = add($db, $_POST['name'], $_POST['firstName'], $_POST['mail'], $_POST['postal']);

            }
            if (isset($_POST["confirm"])) {
                $error = update($db, $_POST["confirm"], $_POST['Nom'], $_POST['Prenom'], $_POST['Mail'], $_POST['Code_Postal']);

            }
            display($db);
            $db->commit();
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
            $db->rollback();
        }

        ?>
    </table>
    <p id="error">
        <?php
        if (isset($error)) {
            echo $error;
        }
        ?>
    </p>
    <form id="formAdd" method="POST">
        <div>
            <input class="inputForm" type="text" name="name" placeholder="Nom">
        </div>
        <input class="inputForm" type="text" name="firstName" placeholder="Prémom">
        </div>
        <input class="inputForm" type="email" name="mail" placeholder="Email">
        </div>
        <input class="inputForm" type="text" name="postal" placeholder="Code-Postal">
        </div>
        <button id="buttonForm" type="submit" name="add"> Ajouter nouvel utilisateur</button>
    </form>
</body>

</html>