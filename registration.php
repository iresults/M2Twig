<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Component\ComponentRegistrar;

call_user_func(
    function () {
        if (class_exists(ComponentRegistrar::class, false)) {
            ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Iresults_M2Twig', __DIR__);
        }
    }
);

