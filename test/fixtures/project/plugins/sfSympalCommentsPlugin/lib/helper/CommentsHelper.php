<?php

function get_sympal_comments($content)
{
  $approvedComments = array();
  foreach ($content->getComments() as $comment)
  {
    if ($comment->isApproved())
    {
      $approvedComments[] = $comment;
    }
  }
  return get_partial('sympal_comments/for_content', array(
    'content' => $content,
    'comments' => $approvedComments,
    'form' => get_sympal_new_comment_form($content)
  ));
}

function get_sympal_new_comment_form($content)
{
  $user = sfContext::getInstance()->getUser();
  $form = new sfSympalNewCommentForm();
  $form->setDefault('content_id', $content->getId());
  
  // if authenticated, set the user_id default value
  if ($user->isAuthenticated())
  {
    $form->setDefault('user_id', $user->getGuardUser()->getId());
  }
  return $form;
}

/**
 * Returns the anchor tag to a comment's website
 * 
 * @param   string $url     The url of the website to link to
 * @param   string $label   The text to include inside the link
 * @param   array  $options An array of link options
 * @return  string
 */
function link_to_sympal_comment_website($comment, $options = array())
{
  if (sfSympalConfig::get('sfSympalCommentsPlugin', 'websites_no_follow'))
  {
    $options['rel'] = 'nofollow';
  }
  
  return link_to($comment['author_name'], $comment['website'], $options);
}