    </div>
  </main>
</div>
<div id="toast" class="toast"></div>
<script>window.CSRF = "<?=csrf_token()?>";</script>
<script src="assets/js/app.js"></script>
<?php if(!empty($pageScript)) echo '<script src="'.$pageScript.'"></script>'; ?>
</body>
</html>
