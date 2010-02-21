<?php echo $form->renderFormTag(url_for('@sympal_create_comment')) ?>
  <input type="hidden" name="from_url" value="<?php echo $sf_request->getParameter('from_url', $sf_request->getUri()) ?>" />
  <table id="comments_form">
    <?php echo $form; ?>
  </table>
  <input type="submit" name="save_comment" value="Save Comment" />
</form>