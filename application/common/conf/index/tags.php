<?php 
/**
 */
return [
    'module_init'=> [
        'application\\index\\behavior\\InitConfig'
    ],
    'action_begin'=> [
        'application\\index\\behavior\\ListenProtectedUrl'
    ]
]
?>