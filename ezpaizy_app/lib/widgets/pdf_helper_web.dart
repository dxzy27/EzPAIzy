import 'dart:html' as html;
import 'dart:ui_web' as ui_web;
import 'package:flutter/material.dart';

Widget getPdfWebView(String url) {
  final viewId = 'pdf-iframe-${url.hashCode}';
  // ignore: undefined_prefixed_name
  ui_web.platformViewRegistry.registerViewFactory(
    viewId,
    (int viewId) => html.IFrameElement()
      ..src = 'https://docs.google.com/gview?embedded=true&url=${Uri.encodeComponent(url)}'
      ..style.border = 'none'
      ..style.width = '100%'
      ..style.height = '100%',
  );
  return HtmlElementView(viewType: viewId);
}
