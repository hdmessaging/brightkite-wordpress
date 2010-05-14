
<?php 
$xml = '<?xml version="1.0" encoding="UTF-8" ?> 
<objects type="array"> 
  <checkin> 
    <place> 
      <longitude type="float">-117.412605</longitude> 
      <scope>address</scope> 
      <name>Costco</name> 
      <id>1be1141a9d38be80ee72b062e7d91df9</id> 
      <latitude type="float">47.727143</latitude> 
      <display_location>7619 N Division St, Spokane, WA, United States</display_location> 
    </place> 
    <via nil="true"></via> 
    <created_at type="datetime">2009-11-17T02:16:03Z</created_at> 
    <creator> 
      <fullname>Michael Lavrisha</fullname> 
      <avatar_url>http://s3.amazonaws.com/bk_store/c1/54/c1544a21ab60a4ba48060dbc09acadeb.png</avatar_url> 
      <small_avatar_url>http://s3.amazonaws.com/bk_store/c1/54/c1544a21ab60a4ba48060dbc09acadeb-small.png</small_avatar_url> 
      <smaller_avatar_url>http://s3.amazonaws.com/bk_store/c1/54/c1544a21ab60a4ba48060dbc09acadeb-smaller.png</smaller_avatar_url> 
      <login>vrish88</login> 
      <tiny_avatar_url>http://s3.amazonaws.com/bk_store/c1/54/c1544a21ab60a4ba48060dbc09acadeb-tiny.png</tiny_avatar_url> 
      <id>895dda8aa47b11dd9ce5003048c0801e</id> 
    </creator> 
    <rating type="integer">0</rating> 
    <public type="boolean">true</public> 
    <view_count type="integer">236</view_count> 
    <comments_count type="integer">0</comments_count> 
    <created_at_ts>1258424163.276000</created_at_ts> 
    <ratings_count type="integer">0</ratings_count> 
    <created_at_as_words>6 days</created_at_as_words> 
    <id>57e43a82de021a82cfa6880b24cee37e</id> 
    <watched nil="true"></watched> 
    <about type="boolean">false</about> 
    <object_type>checkin</object_type> 
  </checkin> 
  <checkin> 
    <place> 
      <longitude type="float">-117.246415</longitude> 
      <scope>address</scope> 
      <name>Chicken-N-More</name> 
      <id>0a59db0c6c41ac9c177d648dac205877</id> 
      <latitude type="float">47.657036</latitude> 
      <display_location>11808 E Sprague Ave, Spokane Valley, WA, United States</display_location> 
    </place> 
    <via nil="true"></via> 
    <created_at type="datetime">2009-10-23T18:50:08Z</created_at> 
    <creator> 
      <fullname>Michael Lavrisha</fullname> 
      <avatar_url>http://s3.amazonaws.com/bk_store/c1/54/c1544a21ab60a4ba48060dbc09acadeb.png</avatar_url> 
      <small_avatar_url>http://s3.amazonaws.com/bk_store/c1/54/c1544a21ab60a4ba48060dbc09acadeb-small.png</small_avatar_url> 
      <smaller_avatar_url>http://s3.amazonaws.com/bk_store/c1/54/c1544a21ab60a4ba48060dbc09acadeb-smaller.png</smaller_avatar_url> 
      <login>vrish88</login> 
      <tiny_avatar_url>http://s3.amazonaws.com/bk_store/c1/54/c1544a21ab60a4ba48060dbc09acadeb-tiny.png</tiny_avatar_url> 
      <id>895dda8aa47b11dd9ce5003048c0801e</id> 
    </creator> 
    <rating type="integer">0</rating> 
    <public type="boolean">true</public> 
    <view_count type="integer">333</view_count> 
    <comments_count type="integer">0</comments_count> 
    <created_at_ts>1256323808.421000</created_at_ts> 
    <ratings_count type="integer">0</ratings_count> 
    <created_at_as_words>about 1 month</created_at_as_words> 
    <id>12a40b5fd1c30425e46ac6029aa13f4e</id> 
    <watched nil="true"></watched> 
    <about type="boolean">false</about> 
    <object_type>checkin</object_type> 
  </checkin> 
</objects>'; 

$xml = simplexml_load_string($xml); 

// echo does the casting for you 
print_r($xml);
foreach($xml->checkin as $checkin) {
  echo $checkin->created_at."\n";
}

?>
