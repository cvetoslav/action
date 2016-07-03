<?php
require_once('page.php');

class RegisterPage extends Page {
    private static $cities = array('Sofia', 'Plovdiv', 'Pleven', 'Varna', 'Burgas', 'Shumen', 'Yambol');

    private function getForm() {
        $index = rand(0, count(self::$cities) - 1);
        $expected = md5(self::$cities[$index]);
        $captcha = str_shuffle(self::$cities[$index]);
        while ($captcha === self::$cities[$index]) {
            $captcha = str_shuffle(self::$cities[$index]);
        }

        $content = '
            <div class="register centered">
                <div class="box">
                    <h2>Create Account</h2>
                    <div class="separator"></div>
                    <form class="register" name="register" action="register" onsubmit="return validateRegistration()" method="post" accept-charset="utf-8">
                        <table class="register">
                            <tr>
                                <td class="left"><b>Username:</b></td>
                                <td class="right">
                                    <input type="text" name="username" placeholder="Username" class="text" minlength=2 maxlength=16 required
                                        title="Username must be between 2 and 16 characters long and can contain only Lattin letters, digits, dots, and underscores. It cannot start with a dot."
                                        onkeyup="validateUsername()">
                                    <i class="fa fa-minus-square" style="color: #D84A38;" id="validationIconUsername"></i>
                                </td>
                            </tr>
                            <tr>
                                <td class="left"><b>First Name:</b></td>
                                <td class="right">
                                    <input type="text" name="name" placeholder="First Name" class="text" minlength=1 maxlength=32 required
                                        title="Names must be between 1 and 32 characters long and can contain only Cyrillic or Lattin letters and dashes."
                                        onkeyup="validateName()">
                                    <i class="fa fa-minus-square" style="color: #D84A38;" id="validationIconName"></i>
                                </td>
                            </tr>
                            <tr>
                                <td class="left"><b>Last Name:</b></td>
                                <td class="right">
                                    <input type="text" name="surname" placeholder="Last Name" class="text" minlength=1 maxlength=32 required
                                        title="Names must be between 1 and 32 characters long and can contain only Cyrillic or Lattin letters and dashes."
                                        onkeyup="validateSurname()">
                                    <i class="fa fa-minus-square" style="color: #D84A38;" id="validationIconSurname"></i>
                                </td>
                            </tr>
                            <tr>
                                <td class="left"><b>Password:</b></td>
                                <td class="right">
                                    <input type="password" name="password1" placeholder="Password" class="text" minlength=1 maxlength=32 required
                                        title="Password must be between 1 and 32 characters long."
                                        onkeyup="validatePassword1()">
                                    <i class="fa fa-minus-square" style="color: #D84A38;" id="validationIconPassword1"></i>
                                </td>
                            </tr>
                            <tr>
                                <td class="left"><b>Repeat Password:</b></td>
                                <td class="right">
                                    <input type="password" name="password2" placeholder="Repeat Password" class="text" minlength=1 maxlength=32 required
                                        title="Password must be between 1 and 32 characters long."
                                        onkeyup="validatePassword2()">
                                    <i class="fa fa-minus-square" style="color: #D84A38;" id="validationIconPassword2"></i>
                                </td>
                            </tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr>
                                <td class="left">E-Mail:</td>
                                <td class="right">
                                    <input type="email" name="email" placeholder="example@mail.com" class="text" minlength=0 maxlength=32
                                        title="E-mail must look like a valid e-mail address."
                                        onkeyup="validateEmail()">
                                    <i class="fa fa-check-square" style="color: #53A93F;" id="validationIconEmail"></i>
                                </td>
                            </tr>
                            <tr>
                                <td class="left">Date of Birth:</td>
                                <td class="right">
                                    <input type="text" name="birthdate" placeholder="YYYY-MM-DD" class="text" minlength=0 maxlength=32
                                        title="The date of birth should follow the ISO format: YYYY-MM-DD."
                                        onkeyup="validateBirthdate()">
                                    <i class="fa fa-check-square" style="color: #53A93F;" id="validationIconBirthdate"></i>
                                </td>
                            </tr>
                            <tr>
                                <td class="left">Town:</td>
                                <td class="right">
                                    <input type="text" name="town" placeholder="Town" class="text" minlength=0 maxlength=32
                                        title="Town must be between 1 and 32 characters long and can contain only Cyrillic or Latin letters and spaces."
                                        onkeyup="validateTown()">
                                    <i class="fa fa-check-square" style="color: #53A93F;" id="validationIconTown"></i>
                                </td>
                            </tr>
                            <tr>
                                <td class="left">Country:</td>
                                <td class="right">
                                    <input type="text" name="country" placeholder="Country" class="text" minlength=0 maxlength=32
                                        title="Country must be between 1 and 32 characters long and can contain only Cyrillic or Latin letters and spaces."
                                        onkeyup="validateCountry()">
                                    <i class="fa fa-check-square" style="color: #53A93F;" id="validationIconCountry"></i>
                                </td>
                            </tr>
                            <tr>
                                <td class="left">Gender:</td>
                                <td class="right">
                                    <input type="radio" name="gender" value="male"> Male<br>
                                    <input type="radio" name="gender" value="female"> Female
                                </td>
                            </tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr>
                                <td class="left"><b>Unscramble the city "' . $captcha . '":</b></td>
                                <td class="right">
                                    <input type="text" name="captcha" class="text" required
                                         title="For example \'cveLho\' should become \'Lovech\'."
                                         onkeyup="validateCaptcha()">
                                    <i class="fa fa-minus-square" style="color: #D84A38;" id="validationIconCaptcha"></i>
                                    <input type="hidden" name="expected" value="' . $expected . '">
                                </td>
                            </tr>
                        </table>
                        <input class="submit" name="submit" type="submit" value="Register"> 
                    </form>
                </div>
            </div>
        ';
        return $content;
    }

    private function validateUsername($username) {
        return preg_match('/^\w[\w.]{1,15}$/', $username);
    }

    private function validateName($name) {
        return preg_match('/(*UTF8)^([A-Za-zА-Яа-я]|-){1,32}$/', $name);
    }

    private function validatePassword($password) {
        return preg_match('/^.{1,32}$/', $password);
    }

    private function validateEmail($email) {
        return $email == '' || preg_match('/^[A-Za-z0-9_.+*=$^-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/', $email);
    }

    private function validateDate($date) {
        return $date == '' || preg_match('/^\d\d\d\d-\d\d-\d\d$/', $date);
    }

    private function validatePlace($town) {
        return $town == '' || preg_match('/(*UTF8)^[A-Za-zА-Яа-я ]{1,32}$/', $town);
    }

    private function validateGender($gender) {
        return $gender == '' || preg_match('/^male|female$/', $gender);
    }

    private function registerUser() {
        // Check captcha question
        if (!isset($_POST['captcha']) || !isset($_POST['expected'])) {
            return 'Could not complete registration: captcha was empty or invalid.';
        }
        if (md5($_POST['captcha']) != $_POST['expected']) {
            return 'Could not complete registration: entered captcha was wrong.';
        }
        if (!in_array($_POST['captcha'], self::$cities)) {
            return 'Could not complete registration: entered captcha was wrong.';
        }
        unset($_POST['captcha']);
        unset($_POST['expected']);

        // Check username
        if (!isset($_POST['username']) || !$this->validateUsername($_POST['username'])) {
            return 'Could not complete registration: username was empty or invalid.';
        }
        $username = $_POST['username']; unset($_POST['username']);
        if (User::findUser($username) != -1) {
            return 'Could not complete registration: username is already taken.';
        }

        // Check first and last names
        if (!isset($_POST['name']) || !$this->validateName($_POST['name'])) {
            return 'Could not complete registration: first name was empty or invalid.';
        }
        $name = $_POST['name']; unset($_POST['name']);
        
        if (!isset($_POST['surname']) || !$this->validateName($_POST['surname'])) {
            return 'Could not complete registration: last name was empty or invalid.';
        }
        $surname = $_POST['surname']; unset($_POST['surname']);

        // Check password
        if (!isset($_POST['password1']) || !isset($_POST['password2'])) {
            return 'Could not complete registration: password was empty.';
        }
        $password1 = $_POST['password1']; unset($_POST['password1']);
        $password2 = $_POST['password2']; unset($_POST['password2']);

        if (strcmp($password1, $password2) != 0) {
            return 'Could not complete registration: passwords did not match.';
        }
        $password = hashPassword($password1);

        // Get optional information
        $email = '';
        if (isset($_POST['email'])) {
            if ($this->validateEmail($_POST['email'])) {
                $email = $_POST['email'];
            } else {
                return 'Could not complete registration: provided e-mail was invalid.';
            }
            unset($_POST['email']);
        }

        $birthdate = '';
        if (isset($_POST['birthdate'])) {
            if ($this->validateDate($_POST['birthdate'])) {
                $birthdate = $_POST['birthdate'];
            } else {
                return 'Could not complete registration: provided date of birth was invalid.';
            }
            unset($_POST['birthdate']);
        }

        $town = '';
        if (isset($_POST['town'])) {
            if ($this->validatePlace($_POST['town'])) {
                $town = $_POST['town'];
            } else {
                return 'Could not complete registration: provided town was invalid.';
            }
            unset($_POST['town']);
        }

        $country = '';
        if (isset($_POST['country'])) {
            if ($this->validatePlace($_POST['country'])) {
                $country = $_POST['country'];
            } else {
                return 'Could not complete registration: provided country was invalid.';
            }
            unset($_POST['country']);
        }

        $gender = '';
        if (isset($_POST['gender'])) {
            if ($this->validateGender($_POST['gender'])) {
                $gender = $_POST['gender'];
            } else {
                return 'Could not complete registration: provided gender was invalid.';
            }
            unset($_POST['gender']);
        }

        // Actually create the user
        if (User::createUser($username, $name, $surname, $password, $email, $birthdate, $town, $country, $gender)) {
            // TODO: Make pretty (add green success icon)
            return 'Registered user "' . $username . '" successfully!';
        } else {
            // TODO: Make pretty (add red error icon)
            return 'Could not complete registration: writing new user failed. Please contact site admin.';
        }
    }

    public function getTitle() {
        return 'O(N)::Register';
    }

    public function getExtraScripts() {
        return array('/scripts/register_form.js', '/scripts/md5.min.js');
    }
    
    public function getContent() {
        if (isset($_POST["username"])) {
            return $this->registerUser();
        }
        return $this->getForm();
    }
    
}

?>