<?php 
/**
 */
return [
    'module_init'=> [
        'application\\admin\\behavior\\InitConfig'
    ],
    'action_begin'=> [
        'application\\admin\\behavior\\ListenLoginStatus',
        'application\\admin\\behavior\\ListenPrivilege',
        'application\\admin\\behavior\\ListenOperate'
    ]
]
?>