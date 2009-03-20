<?php use_stylesheet('/sfSympalCommentsPlugin/css/comments') ?>

<?php $record = $entity->getRecord() ?>
<?php if ($record->getTable()->hasRelation('Comments')): ?>
  <div id="sympal_comments">
    <?php if ($num = count($record['Comments'])): ?>
      <h2>Comments (<?php echo $num ?>)</h2>
      <ul>
        <?php foreach ($record['Comments'] as $comment): ?>
          <li>
            <a name="comment_<?php echo $comment->getId() ?>"></a>
            <h3><?php echo $comment['subject'] ?></h3>
            <small>Posted on <?php echo date('m/d/Y h:i', strtotime($comment['created_at'])) ?> by <?php echo $comment['author_name'] ?>.</small>
            <?php echo sfSympalMarkdownRenderer::convertToHtml($comment['body']) ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <h2>No Comments Created. Be the first to <a href="#form">comment</a>.</h2>
    <?php endif; ?>
  </div>

  <?php if ((sfSympalConfig::get('Comments', 'requires_auth') && $sf_user->isAuthenticated()) || !sfSympalConfig::get('Comments', 'requires_auth')): ?>
    <?php echo $form->renderFormTag(url_for('@sympal_create_comment')) ?>
      <input type="hidden" name="from_url" value="<?php echo $sf_request->getParameter('from_url', $sf_request->getUri()) ?>" />
      <table id="form">
        <?php echo $form; ?>
      </table>
      <input type="submit" name="save" value="Save" />
    </form>
  <?php else: ?>
    <div class="notice">
    You must <?php echo link_to('login', '@sympal_login') ?> to post comments.
    If you don't already have an account then you can <?php echo link_to('register', '@sympal_register') ?>.
    </div>
  <?php endif; ?>
<?php endif; ?>