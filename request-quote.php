<?php
    $this->load->view('include/user_header');
    if($_SESSION['language_id'] != 1) {
        $this->lang->load('ar_lang', 'arabic');
        $language_id = 2;
        $lang_uri = "?lang=ar";
    } else {
        $this->lang->load('en_lang', 'english');
        $language_id = 1;
        $lang_uri = "?lang=en";
    }
?>
<div class="site_wrapper">
    <?php $this->load->view('include/user_menu'); ?>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <!-- inner banner -->
    <?php if (!empty($banners)) { ?>
    <section class="hero-banner height-auto homepage inner-banner">
        <section class="inner-bannerpos d-table w-100">
            <div class="innerbox-position d-table-cell va-middle">
                <div class="container">
                    <div class="banner-content w-55">
                        <?php if ($language_id == 1) { ?>
                        <h1 class="title fc-white mbpx-20 animate-in" data-anim-type="fade-in-right" data-anim-delay="1000">
                            <?php echo $banners{0}->title_en; ?>
                        </h1>
                        <p class="subtitle fc-white fw-normal mbpx-20 animate-in" data-anim-type="fade-in-left" data-anim-delay="2000">
                            <?php echo $banners{0}->description_en; ?>
                        </p>
                        <?php } else { ?>
                        <h1 class="title fc-white mbpx-20 animate-in" data-anim-type="fade-in-right" data-anim-delay="1000">
                            <?php echo $banners{0}->title_ar; ?>
                        </h1>
                        <p class="subtitle fc-white fw-normal mbpx-20 animate-in" data-anim-type="fade-in-left" data-anim-delay="2000">
                            <?php echo $banners{0}->description_ar; ?>
                        </p>
                        <?php } ?>
                        <?php if (!empty($banners{0}->read_more)) { ?>
                        <a href="<?php echo base_url($banners{0}->read_more); ?>" class="btn btn-primary animate-in" data-anim-type="fade-in-right" data-anim-delay="3000"><?php echo $this->lang->line('Read More'); ?></a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </section>
        <img class="img-responsive w100" src="<?php echo PATH_TO_ASSETS; ?>uploads/banners/<?php echo $banners{0}->source; ?>">
    </section>
    <?php } ?>
    <!-- end of inner banner -->
    <div class="clearfix"></div>
    <section class="sec-padding request-padding contact-form">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="smart-forms bmargin request-quote">
                        <h1 class="paddtop1 font-weight-5 text-center animate-in" data-anim-type="fade-in" data-anim-delay="150">
                            Request for a <span class="text-blue-5">Quote</span>
                        </h1>
                        <p class="animate-in" data-anim-type="fade-in" data-anim-delay="300">Looking for a free quote? Share your ideas, vision and requirements with us and your budget requirements. We can definitely work out a viable quote and plan for you, right away.</p>
                        <br/>
                        <form class="requestquote-form animate-in" method="post" action="<?php echo base_url('crayo-admin/processQuotations'); ?>" id="smart-form" data-anim-type="fade-in" data-anim-delay="450">
                        <div>
                            <div class="requestquote_message_container"></div>
                            <div class="section">
                                <label class="field prepend-icon">First Name <span class="red">*</span></label>
                                <input type="text" name="firstname" id="firstname" class="gui-input" placeholder="">
                            </div>
                            <div id="firstname-errorbag" class="errorBag"></div>
                            <!-- end section -->
                            <div class="section">
                                <label class="field prepend-icon">Last Name</label>
                                <input type="text" name="lastname" id="lastname" class="gui-input" placeholder="">
                            </div>
                            <!-- end section -->
                            <div class="section">
                                <label class="field prepend-icon">Email <span class="red">*</span></label>
                                <input type="email" name="email" id="email" class="gui-input" placeholder="">
                            </div>
                            <div id="email-errorbag" class="errorBag"></div>
                            <!-- end section -->
                            <div class="section">
                                <label class="field prepend-icon">Job Title</label>
                                <input type="text" name="jobtitle" id="senderjobtitle" class="gui-input" placeholder="">
                            </div>
                            <!-- end section -->
                            <div class="section">
                                <label class="field prepend-icon">Company <span class="red">*</span></label>
                                <input type="text" name="company" id="company" class="gui-input" placeholder="">
                            </div>
                            <div id="company-errorbag" class="errorBag"></div>
                            <!-- end section -->
                            <div class="section colm colm6">
                                <label class="field prepend-icon">Phone <span class="red">*</span></label>
                                <input type="tel" name="phone" id="phone" class="gui-input" placeholder="">
                            </div>
                            <div id="phone-errorbag" class="errorBag"></div>
                            <!-- end section -->
                            <div class="section">
                                <label class="field prepend-icon">Country <span class="red">*</span></label>
                                <select id="country" class="select" name="country">
                                  <option value="">Select Country</option>
                                  <?php $countries = $this->page_model->getCountries(); ?>
                                  <?php foreach ($countries as $country) { ?>
                                  <option value="<?php echo $country->name; ?>"><?php echo $country->name; ?></option>
                                  <?php } ?>
                                </select>
                            </div>
                            <div id="country-errorbag" class="errorBag"></div>
                            <!-- end section -->
                            <div class="section">
                                <label class="field prepend-icon">I am looking for</label>
                                <select id="looking_for" class="select" name="looking_for">
                                    <option value="services" selected>Services</option>
                                    <option value="products">Products</option>
                                </select>
                            </div>
                            <!-- end section -->
                            <div class="section section-checkbox">
                                <label class="field prepend-icon checkbox-description">What describes your need best</label>
                                <span class="needs_container">
                                <?php $services = $this->page_model->getServicesForContactPage(); ?>
                                <?php foreach ($services as $service) { ?>
                                <label class="field prepend-icon">
                                    <input class="request-checkbox" type="checkbox" name="senderneed[]" value="<?php echo $service->menu_name_english; ?>">
                                    <?php echo $service->menu_name_english; ?>
                                </label>
                                <?php } ?>
                                </span>
                            </div>
                            <!-- end section -->
                            <div class="section">
                                <label class="field prepend-icon">Description of requirement <span class="red">*</span></label>
                                <textarea class="gui-textarea" id="description" name="description" placeholder="State your Requirement"></textarea>
                            </div>
                            <div id="description-errorbag" class="errorBag"></div>
                            <!-- end section -->
                            <div class="section">
                                <label class="field prepend-icon">How did you hear about us?</label>
                                    <select name="heard_from" class="select" id="senderques">
                                        <option value="">Choose any one..</option>
                                        <option value="Google">Google</option>
                                        <option value="Yahoo">Yahoo</option>
                                        <option value="MSN">MSN</option>
                                        <option value="All the Web">All the Web</option>
                                        <option value="Direct Mailer">Direct Mailer</option>
                                        <option value="Word of Mouth">Word of Mouth</option>
                                        <option value="Others">Others</option>
                                    </select>
                            </div>
                            <!-- end section --> 
                            <div class="section section-captcha">
                                <div class="g-recaptcha" data-sitekey="<?php echo $this->config->item('google-recaptcha-key'); ?>" data-callback="showSubmit" data-expired-callback="hideSubmit"></div>
                                <input type="hidden" name="captcha_response" value="" id="captcha_response">
                            </div>
                            <div class="result"></div>
                            <!-- end .result  section -->
                        </div>
                        <?php
                            $csrf = [
                                'name' => $this->security->get_csrf_token_name(),
                                'hash' => $this->security->get_csrf_hash()
                            ];
                        ?>
                        <input type="hidden" name="<?php echo $csrf['name']; ?>" value="<?php echo $csrf['hash']; ?>" />
                        <!-- end .form-body section -->
                        <div class="form-footer">
                            <button type="submit" data-btntext-sending="Sending..." class="button btn-primary pre-request-info" id="request-quote-submit-btn" disabled="disabled">Submit</button>
                            <button type="reset" class="button"> Reset </button>
                        </div>
                        <!-- end .form-footer section -->
                    </form>
                </div>
                <!-- end .smart-forms section --> 
                </div> 
            </div>
        </div>
    </section>
    <!--end section-->
    <div class=" clearfix"></div>
    <section class="section-less-padding clearfix bg-white">
        <div class="container">
            <div class="row">
                <h4 class="text-center">Clients We've Worked With</h4>
                <div class="col-md-12 heroSlider-fixed">
                    <!-- Slider -->
                    <div class="slider responsive">
                    <?php $clients = $this->page_model->getFeaturedClients(); ?>
                    <?php foreach ($clients as $client) { ?>
                        <div>
                            <a href="<?php echo $client->link_to; ?>" title="<?php echo $client->name; ?>">
                                <img class="img-responsive" src="<?php echo PATH_TO_ASSETS; ?>uploads/clients/<?php echo $client->logo; ?>">
                            </a>
                        </div>
                    <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="loader-overlay hidden">
        <div class="loader">
            <span class="fa fa-spinner fa-pulse fa-3x fa-fw"></span>
        </div>
    </div>
    <!--end section -->
    <script>
        // Captcha Callback
        function hideSubmit(e) {
            document.getElementById("request-quote-submit-btn").disabled = true;
        }

        function showSubmit(e) {
            document.getElementById("captcha_response").value = e;
            document.getElementById("request-quote-submit-btn").disabled = false;
        }
    </script>
    <script>
        var services = [];
        var products = [];
        <?php $services = $this->page_model->getServicesForContactPage(); ?>
        <?php foreach ($services as $service) { ?>
        services.push("<?php echo $service->menu_name_english; ?>");
        <?php } ?>
        <?php $products = $this->page_model->getProductsMenu(); ?>
        <?php foreach ($products as $product) { ?>
        products.push("<?php echo $product->menu_name_english; ?>");
        <?php } ?>
    </script>
<?php $this->load->view('include/user_footer'); ?>