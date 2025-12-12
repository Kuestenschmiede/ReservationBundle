
                if ($includedParam !== null) {
                    $includedParamsArr[] = $includedParam;
                }
            }
        }

        if (count($includedParamsArr) > 0) {

            $includedParams = new C4GMultiCheckboxField();
            $includedParams->setModernStyle(false);
            $includedParams->setAllChecked(true);
            $includedParams->setFieldName('included_params');
            $includedParams->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['included_params']);
            $includedParams->setFormField(true);
            $includedParams->setEditable(false);
            $includedParams->setOptions($includedParamsArr);
            $includedParams->setMandatory(false);
            $includedParams->setCondition(array($condition));
            $includedParams->setRemoveWithEmptyCondition(true);
            $includedParams->setAdditionalID($listType['id'] . '-00' . $reservationObject->getId());
            $includedParams->setStyleClass('included-params');
            $includedParams->setNotificationField(true);
            $includedParams->setSort(false);
            $this->fieldList[] = $includedParams;
        }

        $params = $listType['additionalParams'];
        $additionalParamsArr = [];
        //$taxIncl = $GLOBALS['TL_LANG']['fe_c4g_reservation']['taxIncl'];

        if ($params) {
            foreach ($params as $paramId) {
                if ($paramId) {
                    $additionalParam = C4gReservationParamsModel::feParamsCaptions($paramId, $reservationSettings);

                    if ($additionalParam !== null) {
                        $additionalParamsArr[] = $additionalParam;
                    }
                }
            }
        }

        if (count($additionalParamsArr) > 0) {
            if ($listType['additionalParamsFieldType'] == 'radio') {
                $additionalParams = new C4GRadioGroupField();
                $additionalParams->setInitialValue($additionalParamsArr[0]['id']);
                $additionalParams->setSaveAsArray(true);
            } else {
                $additionalParams = new C4GMultiCheckboxField();
                $additionalParams->setModernStyle(false);
            }
            $additionalParams->setFieldName('additional_params');
            $additionalParams->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['additional_params']);
            $additionalParams->setFormField(true);
            $additionalParams->setEditable(true);
            $additionalParams->setOptions($additionalParamsArr);
            $additionalParams->setMandatory($listType['additionalParamsMandatory']);
            $additionalParams->setCondition(array($condition));
            $additionalParams->setRemoveWithEmptyCondition(true);
            $additionalParams->setAdditionalID($listType['id'] . '-00' . $reservationObject->getId());
            $additionalParams->setStyleClass('additional-params');
            $additionalParams->setNotificationField(true);
            $additionalParams->setSort(false);
            $this->fieldList[] = $additionalParams;
        }

        // Append static tail fields from cached blueprint (privacy text, consent, submit, contact meta)
        foreach ($this->getStaticFieldBlueprint() as $bpField) {
            $this->fieldList[] = clone $bpField;
        }

    }

    /**
     * Build and cache the request-invariant tail of the form for the Event handler
     * (privacy text, consent checkbox, submit button, and contact info fields).
     * Uses the reservation settings cache controls (enable + TTL).
     */
    protected function getStaticFieldBlueprint(): array
    {
        $settings = $this->module ? $this->module->getReservationSettings() : null;
        $withPdf = $this->module ? $this->module->isWithDefaultPDFContent() : false;

        // Defaults
        $useCache = true;
        $ttl = 43200; // 12h
        $settingsId = '0';
        try {
            if ($settings && property_exists($settings, 'id')) {
                $settingsId = (string) $settings->id;
            }
            if ($settings && property_exists($settings, 'reservation_enable_cache')) {
                $flag = (string) $settings->reservation_enable_cache;
                $useCache = ($flag === '1' || $flag === 1 || $flag === true);
            }
            if ($settings && property_exists($settings, 'reservation_cache_ttl')) {
                $ttlCandidate = (int) $settings->reservation_cache_ttl;
                if ($ttlCandidate > 0) { $ttl = $ttlCandidate; }
                if ($ttlCandidate === 0) { $ttl = 43200; }
            }
        } catch (\Throwable $t) { /* ignore */ }

        $lang = strtolower((string) ($GLOBALS['TL_LANGUAGE'] ?? ''));
        $buttonCaption = '';
        try {
            $btn = $settings ? ($settings->reservationButtonCaption ?? '') : '';
            if ($btn) { $buttonCaption = C4GUtils::replaceInsertTags($btn); }
        } catch (\Throwable $t) { $buttonCaption = ''; }
        $hasPrivacyText = $settings ? !empty($settings->privacy_policy_text) : false;
        $privacySite = (string) ($settings->privacy_policy_site ?? '');
        $printableFlag = $withPdf ? '1' : '0';

        $blueprint = null;
        $cache = null;
        $cacheKey = 'c4g_reservation_blueprint_event_' . md5(implode('|', [
            $settingsId, $lang, (string) $privacySite, $hasPrivacyText ? '1' : '0',
            (string) $buttonCaption, $printableFlag,
        ]));

        if ($useCache) {
            try {
                $container = System::getContainer();
                $cache = $container && $container->has('cache.app') ? $container->get('cache.app') : null;
                if ($cache) {
                    $item = $cache->getItem($cacheKey);
                    if ($item->isHit()) {
                        $stored = $item->get();
                        if (is_array($stored)) {
                            $blueprint = $stored;
                        }
                    }
                }
            } catch (\Throwable $t) { $cache = null; }
        }

        if ($blueprint === null) {
            $fields = [];

            // Optional privacy policy text
            if ($settings && !empty($settings->privacy_policy_text)) {
                $privacyPolicyText = new C4GTextField();
                $privacyPolicyText->setSimpleTextWithoutEditing(true);
                $privacyPolicyText->setFieldName('privacy_policy_text');
                $privacyPolicyText->setInitialValue(str_replace(' ', '&nbsp;&#x200B;',
                    C4GUtils::replaceInsertTags($settings->privacy_policy_text)));
                $privacyPolicyText->setSize(4);
                $privacyPolicyText->setTableColumn(false);
                $privacyPolicyText->setEditable(false);
                $privacyPolicyText->setDatabaseField(false);
                $privacyPolicyText->setMandatory(false);
                $privacyPolicyText->setNotificationField(false);
                $privacyPolicyText->setStyleClass('privacy-policy-text');
                $privacyPolicyText->setPrintable($withPdf);
                $fields[] = $privacyPolicyText;
            }

            // Consent checkbox with optional link
            if ($settings && $settings->privacy_policy_site) {
                $href = C4GUtils::replaceInsertTags('{{link_url::' . $settings->privacy_policy_site . '}}');
                $desc = '<span class="c4g_field_description_text">' . str_replace(' ', '&nbsp;&#x200B;', $GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed']) . '&nbsp;&#x200B;</span><a href="' . $href . '" target="_blank" rel="noopener">' . str_replace(' ', '&nbsp;&#x200B;', $GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed_link_text']) . '</a>.';
            } else {
                $desc = str_replace(' ', '&nbsp;&#x200B;', $GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed_without_link']);
            }

            $agreedField = new C4GCheckboxField();
            $agreedField->setFieldName('agreed');
            $agreedField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['agreed'].'&nbsp;&#x200B;'.$desc);
            $agreedField->setTableRow(false);
            $agreedField->setColumnWidth(5);
            $agreedField->setSortColumn(false);
            $agreedField->setTableColumn(false);
            $agreedField->setMandatory(true);
            $agreedField->setNotificationField(true);
            $agreedField->setStyleClass('agreed');
            $agreedField->setWithoutDescriptionLineBreak(true);
            $agreedField->setPrintable($withPdf);
            $fields[] = $agreedField;

            // Submit button
            $clickButton = new C4GBrickButton(
                C4GBrickConst::BUTTON_CLICK,
                $buttonCaption ?: $GLOBALS['TL_LANG']['fe_c4g_reservation']['button_reservation'],
                $visible = true,
                $enabled = true,
                $action = '',
                $accesskey = '',
                $defaultByEnter = true
            );
            $buttonField = new C4GButtonField($clickButton);
            $buttonField->setOnClickType(C4GBrickConst::ONCLICK_TYPE_SERVER);
            $buttonField->setOnClick('clickReservation');
            $buttonField->setWithoutLabel(true);
            $fields[] = $buttonField;

            // Contact/location fields (static meta)
            $location_name = new C4GTextField();
            $location_name->setFieldName('location');
            $location_name->setSortColumn(false);
            $location_name->setFormField(false);
            $location_name->setTableColumn(true);
            $location_name->setNotificationField(true);
            $location_name->setPrintable($withPdf);
            $fields[] = $location_name;

            $contact_name = new C4GTextField();
            $contact_name->setFieldName('contact_name');
            $contact_name->setSortColumn(false);
            $contact_name->setFormField(false);
            $contact_name->setTableColumn(true);
            $contact_name->setNotificationField(true);
            $contact_name->setPrintable($withPdf);
            $fields[] = $contact_name;

            $contact_phone = new C4GTelField();
            $contact_phone->setFieldName('contact_phone');
            $contact_phone->setFormField(false);
            $contact_phone->setTableColumn(false);
            $contact_phone->setNotificationField(true);
            $contact_phone->setPrintable($withPdf);
            $fields[] = $contact_phone;

            $contact_email = new C4GEmailField();
            $contact_email->setFieldName('contact_email');
            $contact_email->setTableColumn(false);
            $contact_email->setFormField(false);
            $contact_email->setNotificationField(true);
            $contact_email->setPrintable($withPdf);
            $fields[] = $contact_email;

            $contact_website = new C4GUrlField();
            $contact_website->setFieldName('contact_website');
            $contact_website->setTableColumn(false);
            $contact_website->setFormField(false);
            $contact_website->setNotificationField(true);
            $contact_website->setPrintable($withPdf);
            $fields[] = $contact_website;

            $contact_street = new C4GTextField();
            $contact_street->setFieldName('contact_street');
            $contact_street->setTableColumn(false);
            $contact_street->setFormField(false);
            $contact_street->setNotificationField(true);
            $contact_street->setPrintable($withPdf);
            $fields[] = $contact_street;

            $contact_postal = new C4GTextField();
            $contact_postal->setFieldName('contact_postal');
            $contact_postal->setFormField(false);
            $contact_postal->setTableColumn(false);
            $contact_postal->setNotificationField(true);
            $contact_postal->setPrintable($withPdf);
            $fields[] = $contact_postal;

            $contact_city = new C4GTextField();
            $contact_city->setFieldName('contact_city');
            $contact_city->setTableColumn(false);
            $contact_city->setFormField(false);
            $contact_city->setNotificationField(true);
            $contact_city->setPrintable($withPdf);
            $fields[] = $contact_city;

            $blueprint = $fields;

            if ($useCache && $cache) {
                try {
                    $item = $cache->getItem($cacheKey);
                    $item->set($blueprint);
                    if (method_exists($item, 'expiresAfter')) {
                        $item->expiresAfter($ttl);
                    }
                    $cache->save($item);
                } catch (\Throwable $t) { /* ignore */ }
            }
        }

        return is_array($blueprint) ? $blueprint : [];
    }
}