import { useLayoutEffect } from "react";

export default function useHeroTransition(incomingIndex, reducedMotion) {
  useLayoutEffect(() => {
    if (incomingIndex === null || reducedMotion) {
      return;
    }

    const hero = document.querySelector(".hero");
    const incoming = hero?.querySelector(".hero-incoming");
    const card = hero?.querySelector(".destination-card");

    if (!hero || !incoming || !card) {
      return;
    }

    const heroBounds = hero.getBoundingClientRect();
    const cardBounds = card.getBoundingClientRect();

    const x = cardBounds.left - heroBounds.left;
    const y = cardBounds.top - heroBounds.top;
    const scaleX = cardBounds.width / heroBounds.width;
    const scaleY = cardBounds.height / heroBounds.height;

    const animation = incoming.animate(
      [
        {
          transform: `translate3d(${x}px, ${y}px, 0) scale(${scaleX}, ${scaleY})`,
        },
        {
          transform: "translate3d(0, 0, 0) scale(1, 1)",
        },
      ],
      {
        duration: 1100,
        easing: "cubic-bezier(0.22, 1, 0.36, 1)",
        fill: "both",
      }
    );

    return () => animation.cancel();
  }, [incomingIndex, reducedMotion]);
}