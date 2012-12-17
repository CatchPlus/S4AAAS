<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 9/1/11
 * Time: 8:58 AM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
abstract class UserRole {

//    const
//        TRAINEE     = 2,
//        ANNOTATOR   = 1,
//        VERIFIER    = 3;
      const GUEST_OR_BOT = 1,
            TRAINEE      = 3,
            TRANSCRIBER  = 7,
            VERIFIER     = 15,
            INGEST_ADMIN = 31,
            GLOBAL_ADMIN = 63;

}
