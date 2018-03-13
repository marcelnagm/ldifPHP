<?php

namespace marcel;

class LdapSincronyze {

    static private $ldaphost = '';
    static  private $ldapport = 389;
    static private $username = "cn=admin,dc=uerr,dc=edu,dc=br";
    static private $upasswd = "";
    static private $ldaptree = "OU=usuarios,DC=uerr,DC=edu,DC=br";

    public static function sincron($cn,$email = null,$password =null) {

        $ds = ldap_connect(LdapSincronyze::$ldaphost, LdapSincronyze::$ldapport)
                or die("Could not connect to LdapSincronyze::ldaphost");
        ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
//ldap_set_option($ds, LDAP_OPT_DEBUG_LEVEL, 7);
        if ($ds) {



            $ldapbind = ldap_bind($ds, LdapSincronyze::$username, LdapSincronyze::$upasswd);

            if ($ldapbind) {
                print "Connected!";

                // iterate over array and print data for each entry
//                echo '<h1>Show me the users</h1>';
                 $result = ldap_search($ds, LdapSincronyze::$ldaptree, "(cn=$cn)") or die("Error in search query: " . ldap_error($ldapconn));
                 $data = ldap_get_entries($ds, $result);

                
                for ($i = 0; $i < $data["count"]; $i++) {                    
                        echo "Email: " . $data[$i]["mail"][0] . "<br /><br />";
//                        if ($data[$i]["mail"][0] != $email) {
                            $dn_mod = 'cn='.$cn . ',' . LdapSincronyze::$ldaptree;
                            echo $dn_mod;
                            $attr = array();
                            if(isset($email))    $attr["mail"] = $email;
                            if(isset($password))$attr["userPassword"] =  '{md5}' . base64_encode(pack('H*', md5($password)));
                            $ldap_modf = ldap_mod_replace($ds, $dn_mod, $attr);
                            if ($ldap_modf) {
                                print "Congratulations! modified is authenticated.";
                            } else
                                print "Error";
//                        }
                }
                // print number of entries found
                echo "Number of entries found: " . ldap_count_entries($ds, $result);
            } else {
                print "Access Denied!";
            }
        }
    }

}

LdapSincronyze::sincron('92621333249', 'marcel@uerr.com.br.zt', '123');
?>
