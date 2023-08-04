/*
 * elFinder Integration
 *
 * Copyright (c) 2010-2020, Alexey Sukhotin. All rights reserved.
 */

function elfinder_wymeditor_callback(url) {
  window.opener.jQuery('input.wym_src').val(url);
  window.close();
}
