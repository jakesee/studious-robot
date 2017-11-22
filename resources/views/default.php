<?php
$this->include('cart.twig');

echo 'Hello World';
echo $this->firstName;
echo $this->lastName;
print_r($this->data);
echo $this->src('resources/img/picture.png');

?>