<?php
echo "Friends Phone Numbers, Phone Number,	ID,	Username,	Profile URL<br/>";
for ($i = 1; $i <= 1000; $i++) {
	if($i<100) {
		echo '0703141' . $i. ',100003348845227,lockheart.rd,lockheart.rd<br/>';
	} else {
		echo '070314' . $i . ',100003348845227,lockheart.rd,lockheart.rd<br/>';
	}
}