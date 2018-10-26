<?php
/**
 * Functions for High Flyers Theme
 *
 * @package     highflyers
 * @author      Pascal Brunner <info@pascalbrunner.ch>
 * @copyright
 * @link        https://uht-brunegg.ch
 * @since       1.0.0
 */

add_action( 'wp_enqueue_scripts', 'highflyers_enqueue_styles', 15 );
add_action( 'vb_get_form', 'vb_get_form' );

function highflyers_enqueue_styles() {
    wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), '1.0.0', 'all' );
}

function vb_get_form( ) {

    $pdo = new PDO('mysql:host='.DB_HOST_CUSTOM.';dbname='.DB_NAME_CUSTOM, DB_USER_CUSTOM, DB_PASSWORD_CUSTOM);

    $error = [];
    $teamname = '';
    $prename = '';
    $contact_name = '';
    $street = '';
    $plz = '';
    $city = '';
    $phone = '';
    $email = '';
    $knownfrom = '';
    $category_name = '';
    $success = false;

    if(isset($_POST['submitted'])) {

        if(!isset($_POST['teamname']) || !$_POST['teamname']) array_push($error, 'teamname');
        else $teamname = filter_input(INPUT_POST, 'teamname', FILTER_SANITIZE_STRING);
        if(!isset($_POST['prename']) || !$_POST['prename']) array_push($error, 'prename');
        else $prename = filter_input(INPUT_POST, 'prename', FILTER_SANITIZE_STRING);
        if(!isset($_POST['contact_name']) || !$_POST['contact_name']) array_push($error, 'contact_name');
        else $contact_name = filter_input(INPUT_POST, 'contact_name', FILTER_SANITIZE_STRING);
        if(!isset($_POST['street']) || !$_POST['street']) array_push($error, 'street');
        else $street = filter_input(INPUT_POST, 'street', FILTER_SANITIZE_STRING);
        if(!isset($_POST['plz']) || !$_POST['plz']) array_push($error, 'plz');
        else $plz = filter_input(INPUT_POST, 'plz', FILTER_SANITIZE_NUMBER_INT);
        if(!isset($_POST['city']) || !$_POST['city']) array_push($error, 'city');
        else $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
        if(!isset($_POST['phone']) || !$_POST['phone']) array_push($error, 'phone');
        else $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        if(!isset($_POST['email']) || !$_POST['email']) array_push($error, 'email');
        else $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        if(!isset($_POST['knownfrom']) || !$_POST['knownfrom']) array_push($error, 'knownfrom');
        else $knownfrom = filter_input(INPUT_POST, 'knownfrom', FILTER_SANITIZE_STRING);
        if(!isset($_POST['category']) || !$_POST['category']) array_push($error, 'category');
        else $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_NUMBER_INT);

        if(isset($_POST['honeypot']) && $_POST['honeypot']) array_push($error, 'honeypot');

        if(!$error) {

          $statement = $pdo->prepare("INSERT INTO teams (name, category_id, created_at) VALUES (:name, :category_id, :created_at)");
          $statement->execute(array(':name' => $teamname, ':category_id' => $category, ':created_at' => date("Y-m-d H:i:s")));
          $team_id = $pdo->lastInsertId();

          $statement = $pdo->prepare("SELECT id FROM contacts WHERE email = :email");
          $statement->execute(array(':email' => $email));
          $row = $statement->fetch();
          if($row) {
              $contact_id = $row['id'];
          } else {
              $statement = $pdo->prepare("INSERT INTO contacts (name, prename, street, plz, city, phone, email, knownfrom, created_at) VALUES (:name, :prename, :street, :plz, :city, :phone, :email, :knownfrom, :created_at)");
              $statement->execute(array(':name' => $contact_name, ':prename' => $prename, ':street' => $street, ':plz' => $plz, ':city' => $city, ':phone' => $phone, ':email' => $email, ':knownfrom' => $knownfrom, ':created_at' => date("Y-m-d H:i:s")));
              $contact_id = $pdo->lastInsertId();
          }

          $statement = $pdo->prepare("INSERT INTO team_contact (team_id, contact_id, created_at) VALUES (:team_id, :contact_id, :created_at)");
          $statement->execute(array(':team_id' => $team_id, ':contact_id' => $contact_id, ':created_at' => date("Y-m-d H:i:s")));

          $statement = $pdo->prepare("SELECT name FROM categories WHERE id = :category_id");
          $statement->execute(array(':category_id' => $category));
          $category_name = $statement->fetch();

          $headers[] = 'From: UHT Brunegg <info@unihockey-team-brunegg.ch>';
          $headers[] = 'Content-Type: text/html; charset=UTF-8';
          $subject = 'Anmeldung Unihockey Turnier Brunegg';

          $to = 'info@unihockey-team-brunegg.ch';
          $body = '
            Folgendees Team hat sich für das Turnier angemeldet: <br>
            Teamname: '.$teamname.'<br>
            Kategorie: '.$category_name['name'].'<br>
            Name: '.$prename.' '.$contact_name.'<br>
            Strasse: '.$street.'<br>
            PLZ / Ort: '.$plz.' '.$city.'<br>
            Telefon: '.$phone.'<br>
            Email: '.$email.'<br>
            Wie hast du von unserem Turnier erfahren?: '.$knownfrom.'<br>
          ';
          wp_mail( $to, $subject, $body, $headers );
          //wp_mail( 'praesident@unihockey-team-brunegg.ch', $subject, $body, $headers );

          $to = $email;
          $body = '
            Hallo '.$prename.'<br><br>
            Vielen Dank für deine Anmeldung am Unihockey Turnier in Brunegg.<br><br>
            Du hast folgendes Team angemeldet: <br>
            Teamname: '.$teamname.'<br>
            Kategorie: '.$category_name['name'].'<br>
            Name: '.$prename.' '.$contact_name.'<br>
            Strasse: '.$street.'<br>
            PLZ / Ort: '.$plz.' '.$city.'<br>
            Telefon: '.$phone.'<br>
            Email: '.$email.'<br>
            Wie hast du von unserem Turnier erfahren?: '.$knownfrom.'<br><br>
            Weitere Informationen, sowie den genauen Spielplan erhälst du ca. 1 Woche vor dem Turnier.<br>
            Sportliche Grüsse und bis im März<br>
            High Flyers Brunegg
          ';

          wp_mail( $to, $subject, $body, $headers );

          $error = [];
          $teamname = '';
          $prename = '';
          $contact_name = '';
          $street = '';
          $plz = '';
          $city = '';
          $phone = '';
          $email = '';
          $category_name = '';
          $knownfrom = '';
          $success = true;

        }

    }

    ?>

        <div class="ast-article-single custom-form">
            <?php
                if($error) {
                    ?>
                        <div class="error-container">
                            <p class="error-text">
                                Bitte alle Felder ausfüllen
                            </p>
                        </div>
                    <?php
                }

                if($success) {
                    ?>
                    <div class="success-container">
                        <p class="success-text">
                            Vielen Dank für deine Anmeldung.<br>
                            Du erhälst demnächst eine Bestätigungsemail für deine Anmeldung.<br>
                            Sobald wir einen definitiven Spielplan haben, werden wir ihn dir zusenden.
                        </p>
                    </div>
                    <?php
                }

            ?>
            <form action="" method="post">
                <input type="hidden" name="honeypot" value="" />
                <div class="input-container">
                    <label class="<?= in_array("teamname", $error) ? "error-label" : '' ?>" for="teamname">Teamname*</label>
                    <input class="<?= in_array("teamname", $error) ? "error-input" : '' ?>" type="text" placeholder="Teamname" name="teamname" id="teamname" value="<?= $teamname ?>" />
                </div>
                <div class="input-container">
                    <label for="category">Kategorie*</label>
                    <select name="category" id="category">
                        <?php
                            $counter = 0;
                            $sql = "SELECT * FROM categories WHERE ISNULL(deleted_at)";
                            foreach ($pdo->query($sql) as $row) {
                                if(isset($_POST['category']) && $_POST['category'] == $row['id']) {
                                    echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
                                } else {
                                    echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                                }
                                $counter++;
                                if($counter == 6) echo '<option disabled>──────────</option>';
                            }
                        ?>
                    </select>
                </div>
                <div class="input-container">
                    <label class="<?= in_array("prename", $error) ? "error-label" : '' ?>" for="prename">Vorname*</label>
                    <input class="<?= in_array("prename", $error) ? "error-input" : '' ?>" type="text" placeholder="Vorname" name="prename" id="prename" value="<?= $prename ?>" />
                </div>
                <div class="input-container">
                    <label class="<?= in_array("contact_name", $error) ? "error-label" : '' ?>" for="contact_name">Name*</label>
                    <input class="<?= in_array("contact_name", $error) ? "error-input" : '' ?>" type="text" placeholder="Name" name="contact_name" id="contact_name" value="<?= $contact_name ?>" />
                </div>
                <div class="input-container">
                    <label class="<?= in_array("street", $error) ? "error-label" : '' ?>" for="street">Strasse*</label>
                    <input class="<?= in_array("street", $error) ? "error-input" : '' ?>" type="text" placeholder="Strasse" name="street" id="street" value="<?= $street ?>" />
                </div>
                <div class="input-container-inline">
                    <label class="<?= (in_array("plz", $error) || in_array("city", $error)) ? "error-label" : '' ?>" for="plz">PLZ / Ort*</label>
                    <input class="<?= in_array("plz", $error) ? "error-input" : '' ?> plz-input" class="plz-input" type="text" placeholder="PLZ" name="plz" id="plz" value="<?= $plz ?>" />
                    <input class="<?= in_array("city", $error) ? "error-input" : '' ?> city-input" class="city-input" type="text" placeholder="Ort" name="city" id="city" value="<?= $city ?>" />
                </div>
                <div class="input-container">
                    <label class="<?= in_array("phone", $error) ? "error-label" : '' ?>" for="phone">Telefonnummer*</label>
                    <input class="<?= in_array("phone", $error) ? "error-input" : '' ?>" type="text" placeholder="Telefonnummer" name="phone" id="phone" value="<?= $phone ?>" />
                </div>
                <div class="input-container">
                    <label class="<?= in_array("email", $error) ? "error-label" : '' ?>" for="email">Email*</label>
                    <input class="<?= in_array("email", $error) ? "error-input" : '' ?>" type="text" placeholder="Email" name="email" id="email" value="<?= $email ?>" />
                </div>
                <div class="input-container">
                    <label class="<?= in_array("knownfrom", $error) ? "error-label" : '' ?>" for="knownfrom">Wie hast du von unserem Turnier erfahren?*</label>
                    <input class="<?= in_array("knownfrom", $error) ? "error-input" : '' ?>" type="text" placeholder="Wie hast du von unserem Turnier erfahren?" name="knownfrom" id="knownfrom" value="<?= $knownfrom ?>" />
                </div>
                <div>
                    <input type="submit" value="Absenden" name="submitted" id="submitted" />
                </div>
            </form>
        </div>

    <?php

}
