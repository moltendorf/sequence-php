<?php $f('header.html') ?>
<article id="account-module" class="fancy-box">
    <fieldset>
        <div class="account-log-in">
            <header><h1><?= $l['ACCOUNT_LOG_IN'] ?></h1></header>

            <section>
                <div>
                    <div class="account-email input-text">
                        <input id="account-input-email" name="account-email" type="text" required="required" />
                        <label for="account-input-email"><?= $l['ACCOUNT_EMAIL'] ?></label>
                    </div>

                    <div class="account-password input-text">
                        <input id="account-input-password" name="account-password" type="password"
                               required="required" />
                        <label for="account-input-password"><?= $l['ACCOUNT_PASSWORD'] ?></label>
                    </div>

                    <div class="account-remember-me input-checkbox">
                        <input id="account-input-remember-me" name="account-remember-me" type="checkbox" />
                        <label for="account-input-remember-me"><?= $l['ACCOUNT_REMEMBER_ME'] ?></label>
                    </div>

                    <div class="account-appear-offline input-checkbox">
                        <input id="account-input-appear-offline" name="account-appear-offline" type="checkbox" />
                        <label for="account-input-appear-offline"><?= $l['ACCOUNT_APPEAR_OFFLINE'] ?></label>
                    </div>

                    <input name="account-log-in" class="input-button" type="submit"
                           value="<?= $l['ACCOUNT_LOG_IN'] ?>" />
                </div>
            </section>
        </div>

        <div class="account-or"><h1><?= $l['ACCOUNT_OR'] ?></h1></div>

        <div class="account-sign-up">
            <header><h1><?= $l['ACCOUNT_SIGN_UP'] ?></h1></header>

            <section>
                <div>
                    <p><?= $l['ACCOUNT_SIGN_UP_TEXT'] ?></p>

                    <input name="account-sign-up" class="input-button" type="submit"
                           value="<?= $l['ACCOUNT_SIGN_UP'] ?>" />
                </div>
            </section>
        </div>
    </fieldset>
</article>
<?php $f('footer.html') ?>
