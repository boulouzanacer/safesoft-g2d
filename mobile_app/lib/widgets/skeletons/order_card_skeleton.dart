import 'package:flutter/material.dart';
import 'package:shimmer/shimmer.dart';

class OrderCardSkeleton extends StatelessWidget {
  const OrderCardSkeleton({super.key});

  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: Colors.black.withValues(alpha: 0.06),
      highlightColor: Colors.black.withValues(alpha: 0.02),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(height: 12, width: 120, color: Colors.white),
                const Spacer(),
                Container(height: 24, width: 80, color: Colors.white),
              ],
            ),
            const SizedBox(height: 10),
            Container(height: 10, width: 140, color: Colors.white),
            const SizedBox(height: 10),
            Container(height: 10, width: 180, color: Colors.white),
            const SizedBox(height: 10),
            Container(height: 12, width: 90, color: Colors.white),
          ],
        ),
      ),
    );
  }
}

