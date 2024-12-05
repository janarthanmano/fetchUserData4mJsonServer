<?php
//user class to store user data
class User {
    public string $name;
    public ?string $email;
    public string $phone;
    public string $city;

    public function __construct(
        string $name,
        string $email,
        string $phone,
        string $city
    ){
        $this->name = $name;
        $this->email = $this->validateEmail($email);
        $this->phone = $this->validatePhoneNumber($phone);
        $this->city = $city;
    }

    //function to validate the email address
    private function validateEmail (string $email): ?string
    {
        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
            //return lower case email address if valid
            return strtolower($email); 
        }
        //return null if the email is invalid
        return null; 
    }

    //function to validate phone number
    private function validatePhoneNumber (string $phone): string
    {
        //return phone number after striping off all non-digits characters
        return preg_replace('/\D/', '', $phone);
    }

    //function convert user object to CSV string
    public function csv(): string
    {
        return "\"{$this->name}\",\"{$this->email}\",\"{$this->phone}\",\"{$this->city}\"\n";
    }
}


//user collection class to store user objects
class UserCollection{
    private array $users = [];

    //add user object to the collection
    public function addUser(User $user): void
    {
        $this->users[] = $user;
    }

    //create CSV output on all the user objects stored in the collection.
    public function outputCSV(): void
    {
        foreach ($this->users as $user) {
            echo $user->csv();
        }
    }
}


function fetchUserData(string $url): ?array
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);

    //if error, close curl with error message
    if(curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch) . PHP_EOL;
        curl_close($ch);
        return null;
    }

    curl_close($ch);

    //convert and return json response as associative array
    return json_decode($response, true);
}

//execution starts here

$url = 'https://jsonplaceholder.typicode.com/users';

$userData = fetchUserData($url);

if($userData !== null) {
    $userCollection = new UserCollection();

    foreach($userData as $data) {
        $user = new User(
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['address']['city']
        );

        $userCollection->addUser($user);
    }

    $userCollection->outputCSV();
}else{
    echo "Failed to fetch user data from the JSON server". PHP_EOL;
}
?>