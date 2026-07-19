import 'package:flutter/material.dart';

class LtrValue extends StatelessWidget {
  final Widget child;

  const LtrValue({super.key, required this.child});

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.ltr,
      child: Align(
        alignment: Alignment.centerLeft,
        child: child,
      ),
    );
  }
}

class LtrText extends StatelessWidget {
  final String text;
  final TextStyle? style;
  final int? maxLines;
  final TextOverflow? overflow;

  const LtrText(
    this.text, {
    super.key,
    this.style,
    this.maxLines,
    this.overflow,
  });

  @override
  Widget build(BuildContext context) {
    return LtrValue(
      child: Text(
        text,
        style: style,
        maxLines: maxLines,
        overflow: overflow,
        textAlign: TextAlign.left,
      ),
    );
  }
}
