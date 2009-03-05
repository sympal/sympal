<h1>Sympal Configuration</h1>

<?php echo $form->renderFormTag(url_for('@sympal_config_save')) ?>
  <table>
    <?php echo $form ?>
    <tfoot>
      <tr>
        <td colspan="2"><input type="submit" name="save" value="Save" /></td>
      </tr>
    </tfoot>
  </table>
</form>