<?php echo $form->renderFormTag(url_for('@sympal_save_content_slot?id='.$sf_request->getParameter('id')), array('method' => 'post')) ?>
  <table width="100%">
    <?php echo $form ?>
    <tfoot>
      <tr>
        <td colspan="2">
          <input type="button" id="preview_button" name="preview" value="Preview" onClick="javascript: preview_sympal_content_slot('<?php echo $sf_request->getParameter('id') ?>');" />
          <input type="button" id="save_button" name="save" value="Save" onClick="javascript: save_sympal_content_slot('<?php echo $sf_request->getParameter('id') ?>');" />
        </td>
      </tr>
    </tfoot>
  </table>
</form>