    </div>
  </main>
</div>
<div id="toast" class="toast"></div>
<script>window.CSRF = "<?=csrf_token()?>";</script>
<script src="<?=av('assets/js/app.js')?>"></script>
<?php if(!empty($pageScript)) echo '<script src="'.av($pageScript).'"></script>'; ?>
</body>
</html>
