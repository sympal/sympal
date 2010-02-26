<?php use_helper('Comments') ?>
<?php use_stylesheet('/sfSympalCommentsPlugin/css/comments.css', 'first') ?>

<div id="sympal_comments">
  <?php if ($num = count($comments)): ?>
    <h3>Comments (<?php echo $num ?>) <a href="#comments_form">Add a comment</a></h3>
    <ul>
      <?php foreach ($comments as $comment): ?>
        <li>
          <a name="comment_<?php echo $comment->getId() ?>"></a>
        
          <small>
            Posted on <?php echo date('m/d/Y h:i', strtotime($comment['created_at'])) ?> by
            <?php if ($comment->website && sfSympalConfig::get('sfSympalCommentsPlugin', 'allow_websites')): ?>
              <?php echo link_to_sympal_comment_website($comment) ?>.
            <?php else: ?>
              <?php echo $comment['author_name'] ?>.
            <?php endif; ?>
          </small>
          <?php echo image_tag(get_gravatar_url($comment['author_email_address']), 'align=right') ?>

          <?php echo sfSympalMarkdownRenderer::convertToHtml($comment['body']) ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <h3>No Comments Created. Be the first to comment.</h3>
  <?php endif; ?>
</div>

<?php if ($sf_user->isAuthenticated() || !sfSympalConfig::get('sfSympalCommentsPlugin', 'requires_auth')): ?>
  <?php include_partial('sympal_comments/comment_form', array('form' => $form)); ?>
<?php else: ?>
  <div class="notice">    
    <?php echo __('You must %1% to post comments.', array(
      '%1%' => link_to('signin', '@sympal_signin')
    )) ?>
    <?php echo __('If you don\'t already have an account then you can %1%', array(
      '%1%' => link_to('register', '@sympal_register')
    )) ?>
  </div>
<?php endif; ?>
