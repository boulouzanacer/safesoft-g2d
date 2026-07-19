import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter/material.dart';

import '../../l10n/app_i18n.dart';

class OfflineBanner extends StatefulWidget {
  final Widget child;

  const OfflineBanner({super.key, required this.child});

  @override
  State<OfflineBanner> createState() => _OfflineBannerState();
}

class _OfflineBannerState extends State<OfflineBanner> {
  StreamSubscription<List<ConnectivityResult>>? _sub;
  bool _offline = false;

  @override
  void initState() {
    super.initState();
    _sub = Connectivity().onConnectivityChanged.listen((results) {
      final offline = results.contains(ConnectivityResult.none);
      if (!mounted) return;
      if (_offline != offline) setState(() => _offline = offline);
    });
    Connectivity().checkConnectivity().then((results) {
      final offline = results.contains(ConnectivityResult.none);
      if (!mounted) return;
      setState(() => _offline = offline);
    });
  }

  @override
  void dispose() {
    _sub?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        widget.child,
        if (_offline)
          PositionedDirectional(
            top: MediaQuery.of(context).padding.top,
            start: 0,
            end: 0,
            child: Material(
              color: Colors.amber,
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                child: Row(
                  children: [
                    const Icon(Icons.wifi_off, size: 18),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        context.tr('Mode hors ligne'),
                        style: const TextStyle(fontWeight: FontWeight.w800),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
      ],
    );
  }
}
