<?php $form_id = time(); echo $messages; ?>
<form class="form-login" id="form-login:<?php echo $form_id; ?>" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->username; ?></span>
    <span class="grid span-4">
    <?php echo Form::text('user', Guardian::wayback('user'), null, array(
        'autocomplete' => 'off'
    )); ?>
    </span>
  </label>
  <label class="grid-group">
    <span class="grid span-2 form-label"><?php echo $speak->password; ?></span>
    <span class="grid span-4">
    <?php echo Form::password('pass', null, null, array(
        'autocomplete' => 'off'
    )); ?>
    </span>
  </label>
  <?php $origin = Guardian::wayback('url_origin', $config->manager->slug . '/article'); ?>
  <?php if($origin !== Filter::colon('manager:url', $config->url . '/' . $config->manager->slug . '/logout')): ?>
  <?php echo Form::hidden('kick', Request::get('kick', $origin)); ?>
  <?php endif; ?>
  <div class="grid-group">
    <span class="grid span-2"></span>
    <span class="grid span-4">
    <?php echo Form::button($speak->login, null, null, null, array('class' => array('btn', 'btn-action'))); ?>
    </span>
  </div>
</form>
<script>document.getElementById('form-login:<?php echo $form_id; ?>').user.focus();</script>