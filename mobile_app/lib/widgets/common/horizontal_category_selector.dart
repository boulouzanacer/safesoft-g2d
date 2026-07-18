import 'package:flutter/material.dart';

class HorizontalCategorySelector extends StatefulWidget {
  final List<String> categories;
  final String allLabel;
  final String? selectedValue;
  final ValueChanged<String?> onChanged;
  final EdgeInsetsGeometry padding;

  const HorizontalCategorySelector({
    super.key,
    required this.categories,
    required this.selectedValue,
    required this.onChanged,
    this.allLabel = 'Tous',
    this.padding = EdgeInsets.zero,
  });

  @override
  State<HorizontalCategorySelector> createState() =>
      _HorizontalCategorySelectorState();
}

class _HorizontalCategorySelectorState extends State<HorizontalCategorySelector>
    with SingleTickerProviderStateMixin {
  late final ScrollController _controller;
  late final AnimationController _animationController;
  late final Animation<Offset> _leftSlide;
  late final Animation<Offset> _rightSlide;
  bool _canScrollLeft = false;
  bool _canScrollRight = false;

  @override
  void initState() {
    super.initState();
    _controller = ScrollController()..addListener(_syncScrollState);
    _animationController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    )..repeat(reverse: true);
    _leftSlide = Tween<Offset>(
      begin: Offset.zero,
      end: const Offset(-0.12, 0),
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeInOut,
    ));
    _rightSlide = Tween<Offset>(
      begin: Offset.zero,
      end: const Offset(0.12, 0),
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeInOut,
    ));

    WidgetsBinding.instance.addPostFrameCallback((_) => _syncScrollState());
  }

  @override
  void didUpdateWidget(covariant HorizontalCategorySelector oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.categories.length != widget.categories.length) {
      WidgetsBinding.instance.addPostFrameCallback((_) => _syncScrollState());
    }
  }

  @override
  void dispose() {
    _controller
      ..removeListener(_syncScrollState)
      ..dispose();
    _animationController.dispose();
    super.dispose();
  }

  void _syncScrollState() {
    if (!_controller.hasClients) {
      if (_canScrollLeft || _canScrollRight) {
        setState(() {
          _canScrollLeft = false;
          _canScrollRight = false;
        });
      }
      return;
    }

    final position = _controller.position;
    final canLeft = position.pixels > 8;
    final canRight = position.pixels < position.maxScrollExtent - 8;

    if (canLeft != _canScrollLeft || canRight != _canScrollRight) {
      setState(() {
        _canScrollLeft = canLeft;
        _canScrollRight = canRight;
      });
    }
  }

  Future<void> _scrollBy(double delta) async {
    if (!_controller.hasClients) return;

    final target = (_controller.offset + delta).clamp(
      0.0,
      _controller.position.maxScrollExtent,
    );

    await _controller.animateTo(
      target,
      duration: const Duration(milliseconds: 260),
      curve: Curves.easeOutCubic,
    );
  }

  @override
  Widget build(BuildContext context) {
    final surface = Theme.of(context).colorScheme.surface;
    final shadowColor = Colors.black.withValues(alpha: 0.08);

    return Padding(
      padding: widget.padding,
      child: SizedBox(
        height: 52,
        child: Stack(
          children: [
            NotificationListener<ScrollMetricsNotification>(
              onNotification: (_) {
                WidgetsBinding.instance.addPostFrameCallback(
                  (_) => _syncScrollState(),
                );
                return false;
              },
              child: ListView.separated(
                controller: _controller,
                scrollDirection: Axis.horizontal,
                physics: const BouncingScrollPhysics(),
                padding: const EdgeInsets.symmetric(horizontal: 40),
                itemCount: widget.categories.length + 1,
                separatorBuilder: (_, __) => const SizedBox(width: 8),
                itemBuilder: (_, i) {
                  final isAll = i == 0;
                  final value = isAll ? null : widget.categories[i - 1];
                  final label = isAll ? widget.allLabel : value!;
                  final selected = widget.selectedValue == value;

                  return ChoiceChip(
                    label: Text(
                      label,
                      overflow: TextOverflow.ellipsis,
                    ),
                    selected: selected,
                    onSelected: (_) => widget.onChanged(value),
                    labelPadding: const EdgeInsets.symmetric(horizontal: 4),
                    materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  );
                },
              ),
            ),
            _buildFade(
              alignment: Alignment.centerLeft,
              colors: [
                surface,
                surface.withValues(alpha: 0),
              ],
              visible: _canScrollLeft,
            ),
            _buildFade(
              alignment: Alignment.centerRight,
              colors: [
                surface.withValues(alpha: 0),
                surface,
              ],
              visible: _canScrollRight,
            ),
            _buildArrowButton(
              alignment: Alignment.centerLeft,
              visible: _canScrollLeft,
              shadowColor: shadowColor,
              onTap: () => _scrollBy(-180),
              icon: SlideTransition(
                position: _leftSlide,
                child: const Icon(Icons.chevron_left, size: 18),
              ),
            ),
            _buildArrowButton(
              alignment: Alignment.centerRight,
              visible: _canScrollRight,
              shadowColor: shadowColor,
              onTap: () => _scrollBy(180),
              icon: SlideTransition(
                position: _rightSlide,
                child: const Icon(Icons.chevron_right, size: 18),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFade({
    required Alignment alignment,
    required List<Color> colors,
    required bool visible,
  }) {
    return IgnorePointer(
      child: AnimatedOpacity(
        opacity: visible ? 1 : 0,
        duration: const Duration(milliseconds: 180),
        child: Align(
          alignment: alignment,
          child: Container(
            width: 54,
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: alignment == Alignment.centerLeft
                    ? Alignment.centerLeft
                    : Alignment.centerRight,
                end: alignment == Alignment.centerLeft
                    ? Alignment.centerRight
                    : Alignment.centerLeft,
                colors: colors,
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildArrowButton({
    required Alignment alignment,
    required bool visible,
    required VoidCallback onTap,
    required Widget icon,
    required Color shadowColor,
  }) {
    return Align(
      alignment: alignment,
      child: IgnorePointer(
        ignoring: !visible,
        child: AnimatedOpacity(
          opacity: visible ? 1 : 0,
          duration: const Duration(milliseconds: 180),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 4),
            child: Material(
              color: Colors.white,
              elevation: 3,
              shadowColor: shadowColor,
              shape: const CircleBorder(),
              child: InkWell(
                customBorder: const CircleBorder(),
                onTap: onTap,
                child: SizedBox(
                  width: 28,
                  height: 28,
                  child: Center(child: icon),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
